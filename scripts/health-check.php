#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Timegrid health check script.
 *
 * Usage:
 *   php scripts/health-check.php          # JSON output, exit 0/1
 *   php scripts/health-check.php --quiet  # Exit code only (for Docker HEALTHCHECK)
 *
 * HTTP route: GET /health
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @return array<string, mixed>
 */
function perform_health_check(): array
{
    $checks = [];
    $healthy = true;
    $timestamp = now()->toIso8601String();

    // Database connectivity
    try {
        $start = microtime(true);
        DB::connection()->getPdo();
        DB::connection()->select('SELECT 1');
        $latencyMs = round((microtime(true) - $start) * 1000, 2);

        $checks['database'] = [
            'status' => 'ok',
            'driver' => config('database.default'),
            'latency_ms' => $latencyMs,
        ];
    } catch (Throwable $e) {
        $healthy = false;
        $checks['database'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }

    // Cache connectivity
    try {
        $cacheKey = 'health_check_'.uniqid('', true);
        $testValue = 'ok';

        Cache::put($cacheKey, $testValue, 10);
        $retrieved = Cache::get($cacheKey);
        Cache::forget($cacheKey);

        if ($retrieved !== $testValue) {
            throw new RuntimeException('Cache read/write verification failed');
        }

        $checks['cache'] = [
            'status' => 'ok',
            'driver' => config('cache.default'),
        ];
    } catch (Throwable $e) {
        $healthy = false;
        $checks['cache'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }

    // Disk space
    $storagePath = storage_path();
    $freeBytes = disk_free_space($storagePath);
    $totalBytes = disk_total_space($storagePath);

    if ($freeBytes === false || $totalBytes === false) {
        $healthy = false;
        $checks['disk'] = [
            'status' => 'error',
            'message' => 'Unable to read disk space for storage path',
        ];
    } else {
        $freePercent = round(($freeBytes / $totalBytes) * 100, 2);
        $minFreePercent = (float) env('HEALTH_DISK_MIN_FREE_PERCENT', 10);

        $diskStatus = $freePercent >= $minFreePercent ? 'ok' : 'warning';

        if ($diskStatus === 'warning') {
            $healthy = false;
        }

        $checks['disk'] = [
            'status' => $diskStatus,
            'path' => $storagePath,
            'free_bytes' => $freeBytes,
            'total_bytes' => $totalBytes,
            'free_percent' => $freePercent,
            'threshold_percent' => $minFreePercent,
        ];
    }

    return [
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'service' => config('app.name', 'Timegrid'),
        'environment' => config('app.env', 'production'),
        'timestamp' => $timestamp,
        'checks' => $checks,
    ];
}

// CLI execution
if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    define('LARAVEL_START', microtime(true));

    require __DIR__.'/../vendor/autoload.php';

    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $result = perform_health_check();
    $quiet = in_array('--quiet', $argv, true);

    if (! $quiet) {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    exit($result['status'] === 'healthy' ? 0 : 1);
}

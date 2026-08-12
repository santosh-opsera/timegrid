<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class PiiMaskingProcessor implements ProcessorInterface
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'firstname',
        'lastname',
        'birthdate',
        'password',
        'nin',
        'mobile',
        'phone',
        'national_id',
        'ssn',
    ];

    /** @var list<string> */
    private const ID_KEYS = [
        'business_id',
        'contact_id',
        'user_id',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            return $record->with(
                message: $this->maskString($record->message),
                context: $this->maskValue($record->context),
                extra: $this->maskValue($record->extra),
            );
        } catch (\Throwable) {
            return $record;
        }
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>
     */
    private function maskValue(array $value, ?string $parentKey = null): array
    {
        $masked = [];

        foreach ($value as $key => $item) {
            $stringKey = is_string($key) ? $key : null;
            $masked[$key] = $this->maskMixed($item, $stringKey);
        }

        return $masked;
    }

    private function maskMixed(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && $this->isIdKey($key)) {
            return $value;
        }

        if ($key !== null && $this->isSensitiveKey($key)) {
            return $this->redact($value);
        }

        if (is_array($value)) {
            return $this->maskValue($value, $key);
        }

        if (is_string($value)) {
            return $this->maskString($value);
        }

        return $value;
    }

    private function maskString(string $value): string
    {
        $masked = $this->maskEmailsInString($value);
        $masked = $this->maskPhonesInString($masked);
        $masked = $this->maskNationalIdsInString($masked);

        return $masked;
    }

    private function maskEmailsInString(string $value): string
    {
        return (string) preg_replace_callback(
            '/(?<![\w.-])([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})(?![\w.-])/',
            fn (array $matches): string => $this->maskEmail("{$matches[1]}@{$matches[2]}"),
            $value,
        );
    }

    private function maskPhonesInString(string $value): string
    {
        return (string) preg_replace_callback(
            '/(?<!\d)(?:\+?\d[\d\s().-]{6,}\d)(?!\d)/',
            fn (array $matches): string => $this->maskPhone($matches[0]),
            $value,
        );
    }

    private function maskNationalIdsInString(string $value): string
    {
        $masked = (string) preg_replace(
            '/\b\d{3}-\d{2}-\d{4}\b/',
            '***-**-****',
            $value,
        );

        return (string) preg_replace(
            '/\b(?!(?:\d{1,3}-){3}\d{1,3}\b)\d{9,12}\b/',
            '*********',
            $masked,
        );
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);

        if (count($parts) !== 2) {
            return $this->redact($email);
        }

        [$local, $domain] = $parts;
        $localMasked = ($local !== '' ? $local[0] : '').'***';

        $domainSegments = explode('.', $domain, 2);
        $domainName = $domainSegments[0] ?? '';
        $tld = $domainSegments[1] ?? '';
        $domainMasked = ($domainName !== '' ? $domainName[0] : '').'***';

        if ($tld !== '') {
            $domainMasked .= '.'.$tld;
        }

        return "{$localMasked}@{$domainMasked}";
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '' || strlen($digits) < 4) {
            return '****';
        }

        $lastFour = substr($digits, -4);
        $prefixLength = max(strlen($phone) - 4, 0);

        return str_repeat('*', $prefixLength).$lastFour;
    }

    private function redact(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '[REDACTED]';
        }

        if (is_scalar($value)) {
            return '[REDACTED]';
        }

        return '[REDACTED]';
    }

    private function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }

    private function isIdKey(string $key): bool
    {
        return in_array(strtolower($key), self::ID_KEYS, true);
    }
}

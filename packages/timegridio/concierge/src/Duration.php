<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

/**
 * Converts millisecond intervals into human-readable duration strings.
 */
class Duration
{
    public int $interval = 0;

    private int $tempInterval = 0;

    public string $format = '';

    public function __construct(int|\DateTimeInterface $interval = 0)
    {
        if ($interval instanceof \DateTimeInterface) {
            $interval = 0;
        }

        $this->interval = (int) $interval;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getSeconds(?int $roundMethod = null): float|int
    {
        return $this->round($this->interval / 1000, $roundMethod);
    }

    public function getMinutes(?int $roundMethod = null): float|int
    {
        return $this->round($this->interval / (1000 * 60), $roundMethod);
    }

    public function getHours(?int $roundMethod = null): float|int
    {
        return $this->round($this->interval / (1000 * 60 * 60), $roundMethod);
    }

    /**
     * @param  string|array<string, string>  $format
     */
    public function format(string|array $format = []): string
    {
        $this->tempInterval = $this->interval;
        $hours = $this->getHours(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval % (1000 * 60 * 60);
        $minutes = $this->getMinutes(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval % (1000 * 60);
        $seconds = $this->getSeconds(PHP_ROUND_HALF_DOWN);
        $this->interval = $this->tempInterval;

        if (is_string($format)) {
            $result = strtr($format, [
                '{hours}' => (string) $hours,
                '{minutes}' => (string) $minutes,
                '{seconds}' => (string) $seconds,
            ]);
        } else {
            if ($seconds <= 0) {
                $format['{seconds}'] = '';
            }
            if ($minutes <= 0) {
                $format['{minutes}'] = '';
            }
            if ($hours <= 0) {
                $format['{hours}'] = '';
            }
            $format['{seconds}'] = strtr($format['{seconds}'] ?? '', ['{seconds}' => (string) $seconds]);
            $format['{minutes}'] = strtr($format['{minutes}'] ?? '', ['{minutes}' => (string) $minutes]);
            $format['{hours}'] = strtr($format['{hours}'] ?? '', ['{hours}' => (string) $hours]);
            $result = trim(strtr($format['template'] ?? '', $format));
        }

        return $this->fixSingulars($result);
    }

    private function round(float $result, ?int $roundMethod = null): float|int
    {
        if ($roundMethod === PHP_ROUND_HALF_UP) {
            return (int) ceil($result);
        }

        if ($roundMethod === PHP_ROUND_HALF_DOWN) {
            return (int) floor($result);
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->format($this->format);
    }

    protected function fixSingulars(string $string): string
    {
        $plurals = ['/^1 hours/', '/^1 minutes/', '/^1 seconds/'];
        $singulars = ['1 hour', '1 minute', '1 second'];

        return (string) preg_replace($plurals, $singulars, $string);
    }
}

<?php

declare(strict_types=1);

namespace Ekok\Utils;

class Time
{
    public static function mark(): float
    {
        return microtime(true);
    }

    public static function elapsed(float $start, int $precision = null): float
    {
        return round(static::mark() - $start, $precision ?? 5);
    }

    public static function elapsedTime(float $start, int $precision = null): string
    {
        return static::elapsed($start, $precision) . ' seconds';
    }

    public static function stamp(string $format = null, int $timestamp = null): string
    {
        return date($format ?? 'Y-m-d H:i:s', $timestamp);
    }

    public static function now(string $format = null): string
    {
        return self::stamp($format);
    }
}

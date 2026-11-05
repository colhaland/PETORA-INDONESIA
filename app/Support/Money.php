<?php

declare(strict_types=1);

namespace App\Support;

final class Money
{
    public static function of(mixed $value, int $scale = 2): string
    {
        if (function_exists('bcadd')) {
            $s = is_string($value) ? $value : number_format((float) $value, $scale, '.', '');
            return bcadd($s, '0', $scale);
        }
        $n = is_string($value) ? (float) $value : (float) $value;
        return number_format($n, $scale, '.', '');
    }

    public static function add(mixed $a, mixed $b, int $scale = 2): string
    {
        if (function_exists('bcadd')) {
            return bcadd(self::of($a, $scale), self::of($b, $scale), $scale);
        }
        return number_format((float) self::of($a, $scale) + (float) self::of($b, $scale), $scale, '.', '');
    }

    public static function sub(mixed $a, mixed $b, int $scale = 2): string
    {
        if (function_exists('bcsub')) {
            return bcsub(self::of($a, $scale), self::of($b, $scale), $scale);
        }
        return number_format((float) self::of($a, $scale) - (float) self::of($b, $scale), $scale, '.', '');
    }

    public static function mul(mixed $a, mixed $b, int $scale = 2): string
    {
        if (function_exists('bcmul')) {
            return bcmul(self::of($a, $scale), self::of($b, $scale), $scale);
        }
        return number_format((float) self::of($a, $scale) * (float) self::of($b, $scale), $scale, '.', '');
    }

    public static function cmp(mixed $a, mixed $b, int $scale = 2): int
    {
        if (function_exists('bccomp')) {
            return bccomp(self::of($a, $scale), self::of($b, $scale), $scale);
        }
        $x = (float) self::of($a, $scale);
        $y = (float) self::of($b, $scale);
        return $x <=> $y;
    }

    public static function isPositive(mixed $value, int $scale = 2): bool
    {
        return self::cmp($value, '0', $scale) === 1;
    }

    public static function decimal(mixed $value, int $scale = 2): string
    {
        return self::of($value, $scale);
    }
}

<?php

namespace App\Support;

/**
 * Money is handled in whole cents everywhere. Floats are never used for
 * ringgit - bonuses are the figures people check line by line.
 */
final class Money
{
    public static function fromRinggit(int|float|string $ringgit): int
    {
        return (int) round(((float) $ringgit) * 100);
    }

    public static function toRinggit(int $cents): float
    {
        return $cents / 100;
    }

    public static function format(int $cents): string
    {
        return 'RM'.number_format($cents / 100, 2);
    }

    /** Short form for dashboards: RM40,000 rather than RM40,000.00. */
    public static function formatShort(int $cents): string
    {
        return $cents % 100 === 0
            ? 'RM'.number_format(intdiv($cents, 100))
            : self::format($cents);
    }
}

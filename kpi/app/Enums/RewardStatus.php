<?php

namespace App\Enums;

enum RewardStatus: string
{
    case Calculated = 'calculated';
    case Verified = 'verified';
    case Paid = 'paid';
    case Withheld = 'withheld';

    public function label(): string
    {
        return match ($this) {
            self::Calculated => 'Dikira',
            self::Verified => 'Disahkan',
            self::Paid => 'Dibayar',
            self::Withheld => 'Ditahan',
        };
    }
}

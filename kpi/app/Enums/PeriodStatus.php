<?php

namespace App\Enums;

enum PeriodStatus: string
{
    case Open = 'open';
    case Closing = 'closing';
    case Locked = 'locked';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Dibuka',
            self::Closing => 'Sedang ditutup',
            self::Locked => 'Dikunci',
            self::Reopened => 'Dibuka semula',
        };
    }

    public function acceptsSales(): bool
    {
        return $this !== self::Locked;
    }
}

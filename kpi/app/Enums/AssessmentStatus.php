<?php

namespace App\Enums;

enum AssessmentStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Locked = 'locked';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dihantar',
            self::Approved => 'Diluluskan',
            self::Locked => 'Dikunci',
            self::Reopened => 'Dibuka semula',
        };
    }

    /** Scores are final once approved: no edits, by anyone, without a reopen. */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Reopened], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Locked], true);
    }
}

<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin / Management',
            self::Supervisor => 'Supervisor',
            self::Staff => 'Staf',
        };
    }
}

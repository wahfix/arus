<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Blocked = 'BLOCKED';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::Blocked => 'Diblokir',
        };
    }
}

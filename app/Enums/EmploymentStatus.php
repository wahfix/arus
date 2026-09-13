<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Active = 'ACTIVE';
    case Resigned = 'RESIGNED';
    case Terminated = 'TERMINATED';
    case Unknown = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Resigned => 'Mengundurkan Diri',
            self::Terminated => 'Diberhentikan',
            self::Unknown => 'Tidak Diketahui',
        };
    }
}

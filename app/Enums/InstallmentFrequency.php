<?php

namespace App\Enums;

enum InstallmentFrequency: string
{
    case Monthly = 'MONTHLY';
    case Weekly = 'WEEKLY';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
            self::Weekly => 'Mingguan',
        };
    }
}

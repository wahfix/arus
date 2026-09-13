<?php

namespace App\Enums;

enum InterestMethod: string
{
    case Flat = 'FLAT';
    case ReducingBalance = 'REDUCING_BALANCE';

    public function label(): string
    {
        return match ($this) {
            self::Flat => 'Flat',
            self::ReducingBalance => 'Efektif (Menurun)',
        };
    }
}

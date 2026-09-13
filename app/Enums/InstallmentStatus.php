<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pending = 'PENDING';
    case PartiallyPaid = 'PARTIALLY_PAID';
    case Paid = 'PAID';
    case Overdue = 'OVERDUE';
    case Waived = 'WAIVED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Bayar',
            self::PartiallyPaid => 'Sebagian',
            self::Paid => 'Lunas',
            self::Overdue => 'Menunggak',
            self::Waived => 'Dihapuskan',
        };
    }
}

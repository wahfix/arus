<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case UnderReview = 'UNDER_REVIEW';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case ReadyForDisbursement = 'READY_FOR_DISBURSEMENT';
    case Active = 'ACTIVE';
    case Overdue = 'OVERDUE';
    case Completed = 'COMPLETED';
    case Defaulted = 'DEFAULTED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Diajukan',
            self::UnderReview => 'Dalam Review',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::ReadyForDisbursement => 'Siap Cair',
            self::Active => 'Aktif',
            self::Overdue => 'Menunggak',
            self::Completed => 'Lunas',
            self::Defaulted => 'Macet',
            self::Cancelled => 'Dibatalkan',
        };
    }
}

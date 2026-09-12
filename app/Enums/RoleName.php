<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case LoanOfficer = 'lo';
    case LoanCollector = 'lc';
    case Cashier = 'cashier';
    case CollateralOfficer = 'collateral_officer';
    case IdentityVerifier = 'identity_verifier';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::LoanOfficer => 'Loan Officer',
            self::LoanCollector => 'Loan Collector',
            self::Cashier => 'Cashier',
            self::CollateralOfficer => 'Collateral Officer',
            self::IdentityVerifier => 'Identity Verifier',
            self::Auditor => 'Auditor',
        };
    }
}

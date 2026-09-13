<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class DefaultLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Defaulted;
    }
}

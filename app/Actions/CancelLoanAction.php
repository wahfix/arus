<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class CancelLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Cancelled;
    }
}

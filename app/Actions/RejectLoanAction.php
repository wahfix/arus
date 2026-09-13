<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class RejectLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Rejected;
    }
}

<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class SubmitLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Submitted;
    }
}

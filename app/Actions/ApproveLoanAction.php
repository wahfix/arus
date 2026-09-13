<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class ApproveLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Approved;
    }
}

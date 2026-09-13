<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class CompleteLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Completed;
    }
}

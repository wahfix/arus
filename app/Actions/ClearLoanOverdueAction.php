<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class ClearLoanOverdueAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Active;
    }
}

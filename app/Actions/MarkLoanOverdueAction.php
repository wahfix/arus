<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class MarkLoanOverdueAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Overdue;
    }
}

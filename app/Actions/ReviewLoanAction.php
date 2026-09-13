<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class ReviewLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::UnderReview;
    }
}

<?php

namespace App\Actions;

use App\Enums\LoanStatus;

class PrepareLoanAction extends ChangeLoanStatusAction
{
    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::ReadyForDisbursement;
    }
}

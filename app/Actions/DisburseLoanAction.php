<?php

namespace App\Actions;

use App\Enums\InstallmentFrequency;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Repositories\InstallmentRepository;
use App\Repositories\LoanRepository;
use App\Services\LoanCalculationService;
use App\Services\LoanStateMachine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisburseLoanAction extends ChangeLoanStatusAction
{
    public function __construct(
        protected LoanRepository $loanRepository,
        protected LoanStateMachine $stateMachine,
        protected LoanCalculationService $calculationService,
        protected InstallmentRepository $installmentRepository,
    ) {}

    protected function targetStatus(): LoanStatus
    {
        return LoanStatus::Active;
    }

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): Loan
    {
        return DB::transaction(function () use ($validatedPayload): Loan {
            $loan = $this->loanRepository->findOrFail((int) $validatedPayload['loan_id']);

            $this->stateMachine->transition($loan, LoanStatus::Active, auth()->user());

            $frequency = InstallmentFrequency::tryFrom($loan->installment_frequency)
                ?? throw new RuntimeException('Unknown installment frequency.');

            $plan = $this->calculationService->compute([
                'principal_amount' => $loan->principal_amount,
                'interest_rate_basis_points' => $loan->interest_rate_basis_points,
                'interest_method' => $loan->interest_method,
                'tenor' => $loan->tenor,
                'installment_frequency' => $loan->installment_frequency,
                'first_due_date' => $loan->first_due_date->toDateString(),
            ]);

            $dates = $this->calculationService->installmentDates(
                $loan->first_due_date->toDateString(),
                $frequency,
                $loan->tenor,
            );

            foreach ($plan['schedule'] as $index => $row) {
                $this->installmentRepository->store([
                    'loan_id' => $loan->id,
                    'installment_number' => $row['installment_number'],
                    'due_date' => $dates[$index],
                    'principal_due' => $row['principal_due'],
                    'interest_due' => $row['interest_due'],
                    'penalty_due' => 0,
                    'total_due' => $row['total_due'],
                    'principal_paid' => 0,
                    'interest_paid' => 0,
                    'penalty_paid' => 0,
                    'total_paid' => 0,
                    'remaining_amount' => $row['total_due'],
                    'status' => 'PENDING',
                    'paid_at' => null,
                ]);
            }

            return $loan->fresh();
        });
    }
}

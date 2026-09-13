<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Contracts\Action\RuledActionContract;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Services\LoanCalculationService;
use InvalidArgumentException;

class UpdateLoanAction extends Action implements RuledActionContract
{
    public function __construct(
        protected LoanRepository $loanRepository,
        protected LoanCalculationService $calculationService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'loan_id' => ['required', 'integer', 'exists:loans,id'],
            'principal_amount' => ['required', 'integer', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'between:0,100'],
            'interest_method' => ['required', 'string', 'in:FLAT,REDUCING_BALANCE'],
            'tenor' => ['required', 'integer', 'min:1', 'max:120'],
            'installment_frequency' => ['required', 'string', 'in:MONTHLY,WEEKLY'],
            'disbursement_date' => ['nullable', 'date'],
            'first_due_date' => ['required', 'date', 'after_or_equal:disbursement_date'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): Loan
    {
        $loan = $this->loanRepository->findOrFail((int) $validatedPayload['loan_id']);

        if ($loan->status !== LoanStatus::Draft->value) {
            throw new InvalidArgumentException('Only draft loans can be edited.');
        }

        $rateBasisPoints = $this->calculationService->percentageToBasisPoints((string) $validatedPayload['interest_rate']);
        $plan = $this->calculationService->compute([
            'principal_amount' => (int) $validatedPayload['principal_amount'],
            'interest_rate_basis_points' => $rateBasisPoints,
            'interest_method' => (string) $validatedPayload['interest_method'],
            'tenor' => (int) $validatedPayload['tenor'],
            'installment_frequency' => (string) $validatedPayload['installment_frequency'],
            'first_due_date' => (string) $validatedPayload['first_due_date'],
        ]);

        $this->loanRepository->update($loan->id, [
            'principal_amount' => (int) $validatedPayload['principal_amount'],
            'interest_rate_basis_points' => $rateBasisPoints,
            'interest_method' => (string) $validatedPayload['interest_method'],
            'tenor' => (int) $validatedPayload['tenor'],
            'installment_frequency' => (string) $validatedPayload['installment_frequency'],
            'disbursement_date' => $validatedPayload['disbursement_date'] ?? null,
            'first_due_date' => $validatedPayload['first_due_date'],
            'maturity_date' => $plan['maturity_date'],
            'total_interest' => $plan['total_interest'],
            'total_payable' => $plan['total_payable'],
            'installment_amount' => $plan['installment_amount'],
        ]);

        return $loan->fresh();
    }
}

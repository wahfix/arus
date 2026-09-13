<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Contracts\Action\RuledActionContract;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use App\Services\LoanStateMachine;

abstract class ChangeLoanStatusAction extends Action implements RuledActionContract
{
    public function __construct(
        protected LoanRepository $loanRepository,
        protected LoanStateMachine $stateMachine,
    ) {}

    abstract protected function targetStatus(): LoanStatus;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'loan_id' => ['required', 'integer', 'exists:loans,id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): Loan
    {
        $loan = $this->loanRepository->findOrFail((int) $validatedPayload['loan_id']);

        $this->stateMachine->transition($loan, $this->targetStatus(), auth()->user());

        return $loan->fresh();
    }
}

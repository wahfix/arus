<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanStatusHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LoanStateMachine
{
    /**
     * Allowed transitions keyed by the current status.
     *
     * @var array<string, list<LoanStatus>>
     */
    private const TRANSITIONS = [
        'DRAFT' => [LoanStatus::Submitted, LoanStatus::Cancelled],
        'SUBMITTED' => [LoanStatus::UnderReview, LoanStatus::Cancelled],
        'UNDER_REVIEW' => [LoanStatus::Approved, LoanStatus::Rejected],
        'APPROVED' => [LoanStatus::ReadyForDisbursement],
        'READY_FOR_DISBURSEMENT' => [LoanStatus::Active],
        'ACTIVE' => [LoanStatus::Overdue, LoanStatus::Completed],
        'OVERDUE' => [LoanStatus::Active, LoanStatus::Completed, LoanStatus::Defaulted],
        'REJECTED' => [],
        'CANCELLED' => [],
        'COMPLETED' => [],
        'DEFAULTED' => [],
    ];

    /**
     * @return list<LoanStatus>
     */
    public function transitionsFrom(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    public function can(Loan $loan, LoanStatus $to): bool
    {
        return in_array($to, $this->transitionsFrom($loan->status), true);
    }

    /**
     * Apply a status transition and record it in the history.
     *
     * @throws InvalidArgumentException When the transition is not allowed.
     */
    public function transition(Loan $loan, LoanStatus $to, ?User $actor = null, ?string $notes = null): LoanStatusHistory
    {
        if (! $this->can($loan, $to)) {
            throw new InvalidArgumentException(
                sprintf('Transition %s → %s is not allowed.', $loan->status, $to->value),
            );
        }

        $from = LoanStatus::tryFrom($loan->status) ?? throw new InvalidArgumentException('Unknown loan status.');

        return DB::transaction(function () use ($loan, $from, $to, $actor, $notes): LoanStatusHistory {
            $now = new Carbon;

            $this->applyStatusSideEffects($loan, $from, $to, $actor, $now);

            $loan->status = $to->value;
            $loan->save();

            return $loan->statusHistories()->create([
                'from_status' => $from->value,
                'to_status' => $to->value,
                'changed_by' => $actor?->id,
                'changed_at' => $now,
                'notes' => $notes,
            ]);
        });
    }

    private function applyStatusSideEffects(Loan $loan, LoanStatus $from, LoanStatus $to, ?User $actor, Carbon $now): void
    {
        if ($to === LoanStatus::Approved) {
            $loan->approved_by = $actor?->id;
            $loan->approved_at = $now;
        }

        if ($to === LoanStatus::Active && $from !== LoanStatus::Overdue) {
            $loan->disbursed_at = $now;
            $loan->outstanding_principal = $loan->principal_amount;
            $loan->outstanding_interest = $loan->total_interest;
            $loan->outstanding_penalty = 0;
            $loan->outstanding_total = $loan->total_payable;
        }

        if ($to === LoanStatus::Completed) {
            $loan->completed_at = $now;
            $loan->outstanding_principal = 0;
            $loan->outstanding_interest = 0;
            $loan->outstanding_penalty = 0;
            $loan->outstanding_total = 0;
        }
    }
}

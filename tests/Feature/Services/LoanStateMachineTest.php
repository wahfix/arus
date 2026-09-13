<?php

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanStateMachine;

test('documented transitions are allowed', function () {
    $machine = new LoanStateMachine;

    $cases = [
        ['DRAFT', LoanStatus::Submitted, true],
        ['DRAFT', LoanStatus::Cancelled, true],
        ['SUBMITTED', LoanStatus::UnderReview, true],
        ['SUBMITTED', LoanStatus::Cancelled, true],
        ['UNDER_REVIEW', LoanStatus::Approved, true],
        ['UNDER_REVIEW', LoanStatus::Rejected, true],
        ['APPROVED', LoanStatus::ReadyForDisbursement, true],
        ['READY_FOR_DISBURSEMENT', LoanStatus::Active, true],
        ['ACTIVE', LoanStatus::Overdue, true],
        ['ACTIVE', LoanStatus::Completed, true],
        ['OVERDUE', LoanStatus::Active, true],
        ['OVERDUE', LoanStatus::Completed, true],
        ['OVERDUE', LoanStatus::Defaulted, true],
    ];

    foreach ($cases as [$from, $to, $allowed]) {
        $loan = Loan::factory()->create(['status' => $from]);

        expect($machine->can($loan, $to))->toBe($allowed);
    }
});

test('arbitrary transitions are rejected', function () {
    $machine = new LoanStateMachine;

    $cases = [
        ['DRAFT', LoanStatus::Approved],
        ['SUBMITTED', LoanStatus::Approved],
        ['UNDER_REVIEW', LoanStatus::Active],
        ['APPROVED', LoanStatus::Active],
        ['READY_FOR_DISBURSEMENT', LoanStatus::Approved],
        ['REJECTED', LoanStatus::Approved],
        ['COMPLETED', LoanStatus::Active],
        ['CANCELLED', LoanStatus::Draft],
        ['DEFAULTED', LoanStatus::Active],
    ];

    foreach ($cases as [$from, $to]) {
        $loan = Loan::factory()->create(['status' => $from]);

        expect($machine->can($loan, $to))->toBeFalse();
    }
});

test('transition records a status history entry', function () {
    $machine = new LoanStateMachine;
    $loan = Loan::factory()->create(['status' => 'SUBMITTED']);

    $machine->transition($loan, LoanStatus::UnderReview, User::factory()->create());

    expect($loan->refresh()->status)->toBe('UNDER_REVIEW')
        ->and($loan->statusHistories)->toHaveCount(1)
        ->and($loan->statusHistories->first()->from_status)->toBe('SUBMITTED')
        ->and($loan->statusHistories->first()->to_status)->toBe('UNDER_REVIEW');
});

test('disallowed transition throws and keeps the loan unchanged', function () {
    $machine = new LoanStateMachine;
    $loan = Loan::factory()->create(['status' => 'DRAFT']);

    expect(fn () => $machine->transition($loan, LoanStatus::Approved))
        ->toThrow(InvalidArgumentException::class);

    expect($loan->refresh()->status)->toBe('DRAFT');
});

test('approval records reviewer and time', function () {
    $machine = new LoanStateMachine;
    $approver = User::factory()->create();
    $loan = Loan::factory()->create(['status' => 'UNDER_REVIEW']);

    $machine->transition($loan, LoanStatus::Approved, $approver);

    expect($loan->refresh()->approved_by)->toBe($approver->id)
        ->and($loan->approved_at)->not->toBeNull();
});

test('activation sets disbursement timestamp and opening balances', function () {
    $machine = new LoanStateMachine;
    $loan = Loan::factory()->readyForDisbursement()->create();

    $machine->transition($loan, LoanStatus::Active);

    expect($loan->refresh()->disbursed_at)->not->toBeNull()
        ->and($loan->outstanding_principal)->toBe($loan->principal_amount)
        ->and($loan->outstanding_interest)->toBe($loan->total_interest)
        ->and($loan->outstanding_total)->toBe($loan->total_payable);
});

test('completion zeroes outstanding balances', function () {
    $machine = new LoanStateMachine;
    $loan = Loan::factory()->active()->create();

    $machine->transition($loan, LoanStatus::Completed);

    expect($loan->refresh()->completed_at)->not->toBeNull()
        ->and($loan->outstanding_principal)->toBe(0)
        ->and($loan->outstanding_interest)->toBe(0)
        ->and($loan->outstanding_total)->toBe(0);
});

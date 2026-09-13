<?php

use App\Enums\Permission;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function loanPayload(array $overrides = []): array
{
    return array_merge([
        'customer_id' => Customer::factory()->create()->id,
        'principal_amount' => 10_000_000,
        'interest_rate' => '2',
        'interest_method' => 'FLAT',
        'tenor' => '10',
        'installment_frequency' => 'MONTHLY',
        'disbursement_date' => now()->toDateString(),
        'first_due_date' => now()->addMonth()->toDateString(),
    ], $overrides);
}

test('guests are redirected to the login page', function () {
    $this->get(route('loans.index'))->assertRedirect(route('login'));
    $this->get(route('installments.index'))->assertRedirect(route('login'));
});

test('users without the loan.view permission cannot access loan pages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('loans.index'))
        ->assertForbidden();
});

test('authorized users can list loans', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ViewLoan);
    Loan::factory()->create(['loan_number' => 'ARUS-LOAN-2026-000001']);

    $this->actingAs($user)
        ->get(route('loans.index'))
        ->assertOk()
        ->assertSee('ARUS-LOAN-2026-000001');
});

test('authorized users can view a loan detail page', function () {
    $loan = Loan::factory()->create(['loan_number' => 'ARUS-LOAN-2026-000001']);
    $user = giveUserPermission(User::factory()->create(), Permission::ViewLoan);

    $this->actingAs($user)
        ->get(route('loans.show', $loan))
        ->assertOk()
        ->assertSee($loan->loan_number);
});

test('users with the loan.create permission can create a draft loan with computed amounts', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::CreateLoan);

    $this->actingAs($user)
        ->post(route('loans.store'), loanPayload())
        ->assertRedirect();

    $loan = Loan::query()->latest('id')->firstOrFail();

    expect($loan->status)->toBe('DRAFT')
        ->and($loan->loan_number)->toMatch('/^ARUS-LOAN-'.now()->year.'-\d{6}$/')
        ->and($loan->total_interest)->toBe(2_000_000)
        ->and($loan->total_payable)->toBe(12_000_000)
        ->and($loan->installment_amount)->toBe(1_200_000)
        ->and($loan->interest_rate_basis_points)->toBe(200)
        ->and($loan->created_by)->toBe($user->id)
        ->and($loan->statusHistories)->toHaveCount(1)
        ->and($loan->statusHistories->first()->to_status)->toBe('DRAFT');
});

test('creating a loan requires the loan.create permission', function () {
    $this->actingAs(giveUserPermission(User::factory()->create(), Permission::ViewLoan))
        ->post(route('loans.store'), loanPayload())
        ->assertForbidden();
});

test('preview endpoint returns the computed plan as JSON', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::CreateLoan);

    $this->actingAs($user)
        ->postJson(route('loans.preview'), [
            'principal_amount' => 10_000_000,
            'interest_rate' => '2',
            'interest_method' => 'FLAT',
            'tenor' => '10',
            'installment_frequency' => 'MONTHLY',
            'first_due_date' => now()->toDateString(),
        ])
        ->assertOk()
        ->assertJson([
            'total_interest' => 2_000_000,
            'total_payable' => 12_000_000,
            'installment_amount' => 1_200_000,
        ]);
});

test('draft loans can be submitted', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::SubmitLoan);
    $loan = Loan::factory()->create(['status' => 'DRAFT']);

    $this->actingAs($user)
        ->post(route('loans.submit', $loan))
        ->assertRedirect(route('loans.show', $loan));

    expect($loan->refresh()->status)->toBe('SUBMITTED');
});

test('submitting a loan requires the loan.submit permission', function () {
    $this->actingAs(giveUserPermission(User::factory()->create(), Permission::ViewLoan))
        ->post(route('loans.submit', Loan::factory()->create()))
        ->assertForbidden();
});

test('loan can flow through review, approval, preparation and disbursement', function () {
    $actor = giveUserPermission(
        User::factory()->create(),
        Permission::ApproveLoan,
        Permission::DisburseLoan,
    );

    $loan = Loan::factory()->create(['status' => 'SUBMITTED']);

    $this->actingAs($actor)->post(route('loans.review', $loan));
    expect($loan->refresh()->status)->toBe('UNDER_REVIEW');

    $this->actingAs($actor)->post(route('loans.approve', $loan));
    expect($loan->refresh()->status)->toBe('APPROVED')
        ->and($loan->approved_by)->toBe($actor->id)
        ->and($loan->approved_at)->not->toBeNull();

    $this->actingAs($actor)->post(route('loans.prepare', $loan));
    expect($loan->refresh()->status)->toBe('READY_FOR_DISBURSEMENT');

    $this->actingAs($actor)->post(route('loans.disburse', $loan));
    expect($loan->refresh()->status)->toBe('ACTIVE')
        ->and($loan->disbursed_at)->not->toBeNull()
        ->and($loan->outstanding_principal)->toBe($loan->principal_amount)
        ->and($loan->outstanding_total)->toBe($loan->total_payable)
        ->and($loan->installments)->toHaveCount($loan->tenor)
        ->and($loan->installments->first()->status)->toBe('PENDING')
        ->and($loan->installments->sum('principal_due'))->toBe($loan->principal_amount)
        ->and($loan->installments->sum('interest_due'))->toBe($loan->total_interest)
        ->and($loan->statusHistories)->toHaveCount(4);
});

test('disbursement requires the loan.disburse permission', function () {
    $this->actingAs(giveUserPermission(User::factory()->create(), Permission::ApproveLoan))
        ->post(route('loans.disburse', Loan::factory()->readyForDisbursement()->create()))
        ->assertForbidden();
});

test('under review loans can be rejected', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::RejectLoan);
    $loan = Loan::factory()->create(['status' => 'UNDER_REVIEW']);

    $this->actingAs($user)
        ->post(route('loans.reject', $loan))
        ->assertRedirect(route('loans.show', $loan));

    expect($loan->refresh()->status)->toBe('REJECTED');
});

test('draft and submitted loans can be cancelled', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ManageLoanStatus);

    foreach (['DRAFT', 'SUBMITTED'] as $status) {
        $loan = Loan::factory()->create(['status' => $status]);

        $this->actingAs($user)
            ->post(route('loans.cancel', $loan));

        expect($loan->refresh()->status)->toBe('CANCELLED');
    }
});

test('invalid status transitions are rejected with an error message', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::DisburseLoan, Permission::ManageLoanStatus);
    $loan = Loan::factory()->create(['status' => 'DRAFT']);

    $this->actingAs($user)
        ->post(route('loans.disburse', $loan))
        ->assertRedirect(route('loans.show', $loan))
        ->assertSessionHas('error');

    expect($loan->refresh()->status)->toBe('DRAFT');
});

test('active loans can be marked overdue and completed', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ManageLoanStatus);
    $loan = Loan::factory()->active()->create();

    $this->actingAs($user)->post(route('loans.mark-overdue', $loan));
    expect($loan->refresh()->status)->toBe('OVERDUE');

    $this->actingAs($user)->post(route('loans.complete', $loan));
    expect($loan->refresh()->status)->toBe('COMPLETED')
        ->and($loan->outstanding_total)->toBe(0);
});

test('only draft loans can be edited', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::UpdateLoan);
    $draft = Loan::factory()->create(['status' => 'DRAFT']);
    $submitted = Loan::factory()->create(['status' => 'SUBMITTED']);

    $this->actingAs($user)
        ->get(route('loans.edit', $draft))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('loans.edit', $submitted))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('updating a draft loan recalculates the plan', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::UpdateLoan);
    $loan = Loan::factory()->create(['status' => 'DRAFT', 'principal_amount' => 10_000_000, 'tenor' => 10]);

    $this->actingAs($user)
        ->put(route('loans.update', $loan), [
            'principal_amount' => 20_000_000,
            'interest_rate' => '2',
            'interest_method' => 'FLAT',
            'tenor' => '10',
            'installment_frequency' => 'MONTHLY',
            'disbursement_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($loan->refresh()->principal_amount)->toBe(20_000_000)
        ->and($loan->total_interest)->toBe(4_000_000)
        ->and($loan->total_payable)->toBe(24_000_000);
});

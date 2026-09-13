<?php

use App\Enums\Permission;
use App\Models\Customer;
use App\Models\Employment;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function employmentPayload(array $overrides = []): array
{
    return array_merge([
        'company_name' => 'PT Maju Bersama',
        'department' => 'Finance',
        'position' => 'Analis Kredit',
        'employment_type' => 'FULL_TIME',
        'employment_start_date' => '2020-01-15',
        'estimated_monthly_income' => 7_500_000,
        'employment_status' => 'ACTIVE',
        'notes' => 'Karyawan tetap.',
    ], $overrides);
}

test('users with the employment.manage permission can add employment to a customer', function () {
    $customer = Customer::factory()->create();
    $user = giveUserPermission(User::factory()->create(), Permission::ManageEmployment);

    $this->actingAs($user)
        ->post(route('customers.employments.store', $customer), employmentPayload())
        ->assertRedirect(route('customers.show', $customer));

    expect($customer->employments->count())->toBe(1)
        ->and($customer->employments->first()->company_name)->toBe('PT Maju Bersama');
});

test('users cannot manage employment without the employment.manage permission', function () {
    $customer = Customer::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('customers.employments.store', $customer), employmentPayload())
        ->assertForbidden();
});

test('employment creation enforces server-side validation', function () {
    $customer = Customer::factory()->create();
    $user = giveUserPermission(User::factory()->create(), Permission::ManageEmployment);

    $this->actingAs($user)
        ->post(route('customers.employments.store', $customer), [])
        ->assertSessionHasErrors(['company_name', 'position', 'employment_status']);
});

test('users with the employment.manage permission can update employment', function () {
    $customer = Customer::factory()->create();
    $employment = Employment::factory()->for($customer)->create();
    $user = giveUserPermission(User::factory()->create(), Permission::ManageEmployment);

    $this->actingAs($user)
        ->put(route('customers.employments.update', [$customer, $employment]), employmentPayload([
            'position' => 'Senior Analis Kredit',
        ]))
        ->assertRedirect(route('customers.show', $customer));

    expect($employment->fresh()->position)->toBe('Senior Analis Kredit');
});

test('users with the employment.manage permission can delete employment', function () {
    $customer = Customer::factory()->create();
    $employment = Employment::factory()->for($customer)->create();
    $user = giveUserPermission(User::factory()->create(), Permission::ManageEmployment);

    $this->actingAs($user)
        ->delete(route('customers.employments.destroy', [$customer, $employment]))
        ->assertRedirect(route('customers.show', $customer));

    expect(Employment::query()->find($employment->id))->toBeNull();
});

test('employment routes are scoped to the owning customer', function () {
    $customerA = Customer::factory()->create();
    $customerB = Customer::factory()->create();
    $employment = Employment::factory()->for($customerA)->create();
    $user = giveUserPermission(User::factory()->create(), Permission::ManageEmployment);

    $this->actingAs($user)
        ->put(route('customers.employments.update', [$customerB, $employment]), employmentPayload())
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('customers.employments.destroy', [$customerB, $employment]))
        ->assertNotFound();
});

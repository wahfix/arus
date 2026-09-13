<?php

use App\Enums\Permission;
use App\Models\Customer;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function customerPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Siti Rahayu',
        'national_id_number' => fake()->unique()->numerify('################'),
        'date_of_birth' => '1990-05-10',
        'gender' => 'FEMALE',
        'phone' => '081234567890',
        'email' => 'siti@example.test',
        'address' => 'Jl. Melati No. 1',
        'city' => 'Depok',
        'emergency_contact_name' => 'Andi',
        'emergency_contact_phone' => '081298765432',
        'status' => 'ACTIVE',
    ], $overrides);
}

test('guests are redirected to the login page', function () {
    $this->get(route('customers.index'))
        ->assertRedirect(route('login'));
});

test('users without the customer.view permission cannot access customer pages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('customers.index'))
        ->assertForbidden();
});

test('authorized users can list customers', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ViewCustomer);
    Customer::factory()->create(['full_name' => 'Budi Santoso']);

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('CUS-');
});

test('authorized users can view a customer detail page', function () {
    $customer = Customer::factory()->create(['full_name' => 'Budi Santoso']);
    $user = giveUserPermission(User::factory()->create(), Permission::ViewCustomer);

    $this->actingAs($user)
        ->get(route('customers.show', $customer))
        ->assertOk()
        ->assertSee($customer->full_name);
});

test('users with the customer.create permission can create a customer with a generated code', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::CreateCustomer);

    $this->actingAs($user)
        ->post(route('customers.store'), customerPayload())
        ->assertRedirect();

    $customer = Customer::query()->where('full_name', 'Siti Rahayu')->firstOrFail();

    expect($customer->customer_code)->toMatch('/^CUS-'.now()->year.'-\d{6}$/');
});

test('customer codes are generated sequentially per year', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::CreateCustomer);

    $this->actingAs($user)->post(route('customers.store'), customerPayload());
    $this->actingAs($user)->post(route('customers.store'), customerPayload(['full_name' => 'Dewi Lestari']));

    $codes = Customer::query()->pluck('customer_code')->sort()->values();

    expect($codes)->toHaveCount(2)
        ->and($codes[1])->toMatch('/^CUS-'.now()->year.'-\d{6}$/');
});

test('customer creation enforces server-side validation', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::CreateCustomer);

    $this->actingAs($user)
        ->post(route('customers.store'), [])
        ->assertSessionHasErrors(['full_name', 'national_id_number', 'phone', 'status']);
});

test('customer creation rejects duplicate national id numbers', function () {
    Customer::factory()->create(['national_id_number' => '3201010101010001']);
    $user = giveUserPermission(User::factory()->create(), Permission::CreateCustomer);

    $this->actingAs($user)
        ->post(route('customers.store'), customerPayload(['national_id_number' => '3201010101010001']))
        ->assertSessionHasErrors('national_id_number');
});

test('users without the customer.create permission cannot create customers', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ViewCustomer);

    $this->actingAs($user)
        ->post(route('customers.store'), customerPayload())
        ->assertForbidden();
});

test('users with the customer.update permission can update a customer', function () {
    $customer = Customer::factory()->create(['full_name' => 'Budi Santoso']);
    $user = giveUserPermission(User::factory()->create(), Permission::UpdateCustomer);

    $this->actingAs($user)
        ->put(route('customers.update', $customer), customerPayload([
            'full_name' => 'Budi Santoso Baru',
            'national_id_number' => $customer->national_id_number,
        ]))
        ->assertRedirect(route('customers.show', $customer));

    expect($customer->fresh()->full_name)->toBe('Budi Santoso Baru');
});

test('users with the customer.delete permission can delete a customer', function () {
    $customer = Customer::factory()->create();
    $user = giveUserPermission(User::factory()->create(), Permission::DeleteCustomer);

    $this->actingAs($user)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});

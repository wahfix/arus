<?php

use App\Enums\Permission;
use App\Models\Installment;
use App\Models\User;

test('users without the installment.view permission cannot access installment pages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('installments.index'))
        ->assertForbidden();
});

test('authorized users can list installments', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ViewInstallment);
    $installment = Installment::factory()->create(['status' => 'PENDING']);

    $this->actingAs($user)
        ->get(route('installments.index'))
        ->assertOk()
        ->assertSee($installment->loan->loan_number)
        ->assertSee('Belum Bayar');
});

test('installments can be filtered by status', function () {
    $user = giveUserPermission(User::factory()->create(), Permission::ViewInstallment);
    Installment::factory()->create(['status' => 'PENDING']);
    Installment::factory()->create(['status' => 'OVERDUE']);

    $this->actingAs($user)
        ->get(route('installments.index', ['status' => 'OVERDUE']))
        ->assertOk()
        ->assertSee('Menunggak');
});

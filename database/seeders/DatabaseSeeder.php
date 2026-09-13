<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\RoleName;
use App\Models\Customer;
use App\Models\Employment;
use App\Models\Permission as PermissionRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedDemoUsers();
        $this->seedDemoCustomers();
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionRecord::query()->updateOrCreate(
                ['name' => $permission->value],
                ['label' => str($permission->value)->headline()->toString()],
            );
        }

        $permissionIds = PermissionRecord::query()->pluck('id', 'name');

        foreach ($this->rolePermissions() as $roleName => $permissions) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName],
                ['label' => RoleName::tryFrom($roleName)?->label() ?? $roleName],
            );

            $role->permissions()->sync(
                collect($permissions)
                    ->map(fn (Permission $permission) => $permissionIds->get($permission->value))
                    ->filter()
                    ->all(),
            );
        }
    }

    private function seedDemoUsers(): void
    {
        $users = [
            ['admin@example.test', 'Admin User', RoleName::Admin],
            ['lo@example.test', 'Loan Officer', RoleName::LoanOfficer],
            ['lc@example.test', 'Loan Collector', RoleName::LoanCollector],
            ['cashier@example.test', 'Cashier', RoleName::Cashier],
            ['collateral@example.test', 'Collateral Officer', RoleName::CollateralOfficer],
            ['verifier@example.test', 'Identity Verifier', RoleName::IdentityVerifier],
            ['auditor@example.test', 'Auditor', RoleName::Auditor],
        ];

        foreach ($users as [$email, $name, $roleName]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password', // Development-only password.
                    'email_verified_at' => now(),
                ],
            );

            $user->roles()->sync([Role::query()->where('name', $roleName->value)->value('id')]);
        }
    }

    private function seedDemoCustomers(): void
    {
        if (Customer::query()->exists()) {
            return;
        }

        Customer::factory()
            ->count(20)
            ->create()
            ->each(function (Customer $customer): void {
                Employment::factory()
                    ->count(fake()->numberBetween(0, 3))
                    ->for($customer)
                    ->create();
            });
    }

    /**
     * @return array<RoleName, list<Permission>>
     */
    private function rolePermissions(): array
    {
        return [
            RoleName::Admin->value => Permission::cases(),
            RoleName::LoanOfficer->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::CreateCustomer,
                Permission::UpdateCustomer,
                Permission::ViewEmployment,
                Permission::ManageEmployment,
                Permission::ViewLoan,
                Permission::CreateLoan,
                Permission::UpdateLoan,
                Permission::SubmitLoan,
            ],
            RoleName::LoanCollector->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::ViewLoan,
                Permission::ViewInstallment,
                Permission::ViewPayment,
                Permission::ViewCollection,
                Permission::CreateCollection,
            ],
            RoleName::Cashier->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::ViewLoan,
                Permission::ViewPayment,
                Permission::CreatePayment,
                Permission::ReversePayment,
            ],
            RoleName::CollateralOfficer->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::ViewLoan,
                Permission::ViewCollateral,
                Permission::ReceiveCollateral,
                Permission::UpdateCollateralCustody,
                Permission::PrepareCollateralRelease,
                Permission::ReleaseCollateral,
            ],
            RoleName::IdentityVerifier->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::ViewLoan,
                Permission::ViewIdentityVerification,
                Permission::VerifyIdentity,
            ],
            RoleName::Auditor->value => [
                Permission::ViewDashboard,
                Permission::ViewCustomer,
                Permission::ViewLoan,
                Permission::ViewInstallment,
                Permission::ViewPayment,
                Permission::ViewCollateral,
                Permission::ViewIdentityVerification,
                Permission::ViewCollection,
                Permission::ViewReport,
                Permission::ViewAuditLog,
            ],
        ];
    }
}

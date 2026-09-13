<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        return [
            'loan_number' => 'ARUS-LOAN-'.now()->year.'-'.str_pad(
                (string) $this->faker->unique()->numberBetween(1, 999999),
                6,
                '0',
                STR_PAD_LEFT
            ),
            'customer_id' => Customer::factory(),
            'principal_amount' => 10000000,
            'interest_rate_basis_points' => 200,
            'interest_method' => 'FLAT',
            'tenor' => 3,
            'installment_frequency' => 'MONTHLY',
            'disbursement_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'maturity_date' => now()->addMonths(3)->toDateString(),
            'total_interest' => 600000,
            'total_payable' => 10600000,
            'installment_amount' => 3533333,
            'outstanding_principal' => 0,
            'outstanding_interest' => 0,
            'outstanding_penalty' => 0,
            'outstanding_total' => 0,
            'status' => 'DRAFT',
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'disbursed_at' => null,
            'completed_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => ['status' => 'SUBMITTED']);
    }

    public function underReview(): static
    {
        return $this->state(fn (): array => ['status' => 'UNDER_REVIEW']);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => 'APPROVED',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function readyForDisbursement(): static
    {
        return $this->approved()->state(fn (): array => ['status' => 'READY_FOR_DISBURSEMENT']);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => 'ACTIVE',
            'disbursed_at' => now(),
            'outstanding_principal' => 10000000,
            'outstanding_interest' => 600000,
            'outstanding_total' => 10600000,
        ]);
    }

    public function overdue(): static
    {
        return $this->active()->state(fn (): array => ['status' => 'OVERDUE']);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'COMPLETED',
            'completed_at' => now(),
            'outstanding_principal' => 0,
            'outstanding_interest' => 0,
            'outstanding_total' => 0,
        ]);
    }
}

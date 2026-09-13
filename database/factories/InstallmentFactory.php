<?php

namespace Database\Factories;

use App\Models\Installment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installment>
 */
class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'installment_number' => $this->faker->unique()->numberBetween(1, 120),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'principal_due' => 3333333,
            'interest_due' => 200000,
            'penalty_due' => 0,
            'total_due' => 3533333,
            'principal_paid' => 0,
            'interest_paid' => 0,
            'penalty_paid' => 0,
            'total_paid' => 0,
            'remaining_amount' => 3533333,
            'status' => 'PENDING',
            'paid_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employment>
 */
class EmploymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'company_name' => fake()->company(),
            'department' => fake()->randomElement(['Finance', 'Marketing', 'Operations', 'IT', 'HR', null]),
            'position' => fake()->jobTitle(),
            'employment_type' => fake()->randomElement(['FULL_TIME', 'PART_TIME', 'CONTRACT', 'SELF_EMPLOYED']),
            'employment_start_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'estimated_monthly_income' => fake()->randomElement([4_000_000, 5_500_000, 7_250_000, 9_000_000, 12_500_000, 15_000_000, 20_000_000, 25_000_000]),
            'employment_status' => fake()->randomElement(['ACTIVE', 'ACTIVE', 'ACTIVE', 'RESIGNED', 'TERMINATED']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}

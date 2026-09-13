<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected static int $codeSequence = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = now()->year;
        self::$codeSequence++;

        return [
            'customer_code' => 'CUS-'.$year.'-'.str_pad((string) self::$codeSequence, 6, '0', STR_PAD_LEFT),
            'full_name' => fake()->name(),
            'national_id_number' => fake()->unique()->numerify('################'),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-21 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['MALE', 'FEMALE']),
            'phone' => '08'.substr((string) fake()->unique()->numerify('##########'), 0, 11),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement([
                'Jakarta Selatan',
                'Jakarta Utara',
                'Depok',
                'Bekasi',
                'Tangerang',
                'Bogor',
                'Bandung',
                'Surabaya',
                'Semarang',
                'Yogyakarta',
            ]),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '08'.substr((string) fake()->unique()->numerify('##########'), 0, 11),
            'status' => fake()->randomElement(['ACTIVE', 'ACTIVE', 'ACTIVE', 'INACTIVE']),
        ];
    }
}

<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Contracts\Action\RuledActionContract;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CreateCustomerAction extends Action implements RuledActionContract
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'national_id_number' => ['required', 'string', 'max:20', 'unique:customers,national_id_number'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:20'],
            'phone' => ['required', 'string', 'regex:/^[0-9+][0-9]{9,14}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'regex:/^[0-9+][0-9]{9,14}$/'],
            'status' => ['required', 'string', 'in:ACTIVE,INACTIVE,BLOCKED'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): Customer
    {
        $year = now()->year;

        $validatedPayload['customer_code'] = 'CUS-'.$year.'-'.str_pad(
            (string) ($this->customerRepository->countForYear($year) + 1),
            6,
            '0',
            STR_PAD_LEFT
        );

        return $this->customerRepository->store($validatedPayload);
    }
}

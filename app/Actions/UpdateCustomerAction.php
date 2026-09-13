<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Contracts\Action\RuledActionContract;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateCustomerAction extends Action implements RuledActionContract
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'customer_id' => ['required', 'integer'],
            'full_name' => ['required', 'string', 'max:255'],
            'national_id_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'national_id_number')->ignore($payload['customer_id']),
            ],
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
    protected function handler($payload = null, array $validatedPayload = []): bool
    {
        return $this->customerRepository->update(
            (int) $validatedPayload['customer_id'],
            Arr::except($validatedPayload, ['customer_id'])
        );
    }
}

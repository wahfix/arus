<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function generateCustomerCode(): string
    {
        $year = now()->year;

        return 'CUS-'.$year.'-'.str_pad((string) ($this->customers->countForYear($year) + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        $data['customer_code'] = $this->generateCustomerCode();

        return $this->customers->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        return $this->customers->update($customer, $data);
    }

    public function delete(Customer $customer): bool
    {
        return $this->customers->delete($customer);
    }
}

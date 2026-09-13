<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Employment;

class EmploymentRepository
{
    public function find(int $id, ?int $customerId = null): Employment
    {
        return Employment::query()
            ->when($customerId !== null, fn ($query) => $query->where('customer_id', $customerId))
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForCustomer(Customer $customer, array $data): Employment
    {
        return $customer->employments()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Employment $employment, array $data): Employment
    {
        $employment->update($data);

        return $employment;
    }

    public function delete(Employment $employment): bool
    {
        return $employment->delete();
    }
}

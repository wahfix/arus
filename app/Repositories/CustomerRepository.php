<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->search($search)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Customer
    {
        return Customer::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    public function countForYear(int $year): int
    {
        return Customer::query()
            ->where('customer_code', 'like', "CUS-{$year}-%")
            ->count();
    }
}

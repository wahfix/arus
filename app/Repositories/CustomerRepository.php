<?php

namespace App\Repositories;

use App\Abstractions\Repository\ModelRepository;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends ModelRepository<Customer>
 */
class CustomerRepository extends ModelRepository
{
    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginateSearch(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->search($search)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countForYear(int $year): int
    {
        return $this->query()
            ->where('customer_code', 'like', "CUS-{$year}-%")
            ->count();
    }
}

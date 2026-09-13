<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCustomersAction extends Action
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * @param  array<string, mixed>  $validatedPayload
     * @return LengthAwarePaginator<int, Customer>
     */
    protected function handler($payload = null, array $validatedPayload = []): LengthAwarePaginator
    {
        $keyword = is_string($payload) ? $payload : null;

        return $this->customerRepository->paginateSearch($keyword);
    }
}

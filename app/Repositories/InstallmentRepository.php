<?php

namespace App\Repositories;

use App\Abstractions\Repository\ModelRepository;
use App\Models\Installment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends ModelRepository<Installment>
 */
class InstallmentRepository extends ModelRepository
{
    /**
     * @return LengthAwarePaginator<int, Installment>
     */
    public function paginateSearch(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('loan.customer')
            ->whereStatus($status)
            ->where(function ($query) use ($search): void {
                if ($search !== null && trim($search) !== '') {
                    $query->where('installment_number', 'like', "%{$search}%")
                        ->orWhereHas('loan', function ($loan) use ($search): void {
                            $loan->where('loan_number', 'like', "%{$search}%")
                                ->orWhereHas('customer', function ($customer) use ($search): void {
                                    $customer->where('full_name', 'like', "%{$search}%");
                                });
                        });
                }
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

<?php

namespace App\Repositories;

use App\Abstractions\Repository\ModelRepository;
use App\Models\Loan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends ModelRepository<Loan>
 */
class LoanRepository extends ModelRepository
{
    /**
     * @return LengthAwarePaginator<int, Loan>
     */
    public function paginateSearch(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('customer')
            ->search($search)
            ->whereStatus($status)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countForYear(int $year): int
    {
        return $this->query()
            ->where('loan_number', 'like', "ARUS-LOAN-{$year}-%")
            ->count();
    }
}

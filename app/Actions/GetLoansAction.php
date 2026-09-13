<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetLoansAction extends Action
{
    public function __construct(protected LoanRepository $loanRepository) {}

    /**
     * @param  array{q?: string|null, status?: string|null}  $payload
     * @return LengthAwarePaginator<int, Loan>
     */
    protected function handler($payload = null, array $validatedPayload = []): LengthAwarePaginator
    {
        $payload = is_array($payload) ? $payload : [];

        return $this->loanRepository->paginateSearch(
            isset($payload['q']) ? $payload['q'] : null,
            isset($payload['status']) ? $payload['status'] : null,
        );
    }
}

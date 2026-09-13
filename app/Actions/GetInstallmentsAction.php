<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Models\Installment;
use App\Repositories\InstallmentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetInstallmentsAction extends Action
{
    public function __construct(protected InstallmentRepository $installmentRepository) {}

    /**
     * @param  array{q?: string|null, status?: string|null}  $payload
     * @return LengthAwarePaginator<int, Installment>
     */
    protected function handler($payload = null, array $validatedPayload = []): LengthAwarePaginator
    {
        $payload = is_array($payload) ? $payload : [];

        return $this->installmentRepository->paginateSearch(
            isset($payload['q']) ? $payload['q'] : null,
            isset($payload['status']) ? $payload['status'] : null,
        );
    }
}

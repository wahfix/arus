<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Models\Employment;
use App\Repositories\EmploymentRepository;
use InvalidArgumentException;

class DeleteEmploymentAction extends Action
{
    public function __construct(protected EmploymentRepository $employmentRepository) {}

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): bool
    {
        if (! $payload instanceof Employment) {
            throw new InvalidArgumentException('Expected Employment instance as payload.');
        }

        return $this->employmentRepository->delete($payload->id);
    }
}

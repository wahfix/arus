<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use InvalidArgumentException;

class DeleteCustomerAction extends Action
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): bool
    {
        if (! $payload instanceof Customer) {
            throw new InvalidArgumentException('Expected Customer instance as payload.');
        }

        return $this->customerRepository->delete($payload->id);
    }
}

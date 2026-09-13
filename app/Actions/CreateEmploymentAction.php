<?php

namespace App\Actions;

use App\Abstractions\Actions\Action;
use App\Contracts\Action\RuledActionContract;
use App\Models\Employment;
use App\Repositories\EmploymentRepository;
use Illuminate\Validation\Rule;

class CreateEmploymentAction extends Action implements RuledActionContract
{
    public function __construct(protected EmploymentRepository $employmentRepository) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'company_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'employment_start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'estimated_monthly_income' => ['nullable', 'integer', 'min:0'],
            'employment_status' => ['required', 'string', 'in:ACTIVE,RESIGNED,TERMINATED,UNKNOWN'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validatedPayload
     */
    protected function handler($payload = null, array $validatedPayload = []): Employment
    {
        return $this->employmentRepository->store($validatedPayload);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmploymentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
}

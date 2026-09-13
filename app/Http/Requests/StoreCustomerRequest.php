<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'national_id_number' => ['required', 'string', 'max:20', 'unique:customers,national_id_number'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:20'],
            'phone' => ['required', 'string', 'regex:/^[0-9+][0-9]{9,14}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'regex:/^[0-9+][0-9]{9,14}$/'],
            'status' => ['required', 'string', 'in:ACTIVE,INACTIVE,BLOCKED'],
        ];
    }
}

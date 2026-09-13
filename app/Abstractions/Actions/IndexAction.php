<?php

namespace App\Abstractions\Actions;

use App\Contracts\Action\RuledActionContract;

abstract class IndexAction extends Action implements RuledActionContract
{
    /**
     * Get the validation rules for the index action.
     *
     * @param  array<string, mixed>  $payload  The data for the action.
     * @return array<string, mixed>
     */
    public function rules(array $payload): array
    {
        return [
            'keyword' => ['nullable', 'string'],
            'columns' => ['nullable', 'array'],
            'limit' => ['nullable', 'numeric'],
        ];
    }
}

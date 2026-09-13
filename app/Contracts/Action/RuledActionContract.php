<?php

namespace App\Contracts\Action;

interface RuledActionContract
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function rules(array $payload): array;
}

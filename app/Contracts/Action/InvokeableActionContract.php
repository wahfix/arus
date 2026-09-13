<?php

namespace App\Contracts\Action;

interface InvokeableActionContract
{
    /**
     * @param  array<string, mixed>  $payload
     * @return mixed
     */
    public function __invoke(array $payload);
}

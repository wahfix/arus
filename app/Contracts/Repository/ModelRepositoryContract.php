<?php

namespace App\Contracts\Repository;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ModelRepositoryContract extends RepositoryContract
{
    public static function resolve(string $modelName): static;

    /**
     * @return Builder<Model>
     */
    public function query(?Closure $callable = null): Builder;

    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * @param  array<int, string>  $columns
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * @param  array<int, string>  $columns
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    /**
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * @param  array<int, string>  $columns
     */
    public function findBySlug(string $slug, array $columns = ['*']): ?Model;

    /**
     * @param  string|array<string, mixed>  $relations
     * @return Builder<Model>
     */
    public function with($relations): Builder;
}

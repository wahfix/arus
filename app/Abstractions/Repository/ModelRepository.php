<?php

namespace App\Abstractions\Repository;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @template TModel of Model
 */
abstract class ModelRepository
{
    /**
     * The Eloquent model instance.
     *
     * @var TModel
     */
    protected Model $model;

    /**
     * @param  TModel|null  $model  An optional model instance to override name resolution.
     */
    public function __construct(?Model $model = null)
    {
        /** @var TModel|null $model */
        $this->model = $model ?? $this->resolveModel();
    }

    final public static function getNamespace(): string
    {
        return 'App\Repositories';
    }

    final public static function resolve(string $modelName): static
    {
        $class = static::getNamespace().'\\'.Str::studly($modelName).'Repository';

        /** @var class-string<static> $class */
        return new $class;
    }

    /**
     * Resolve the model class from the repository class name.
     *
     * @return class-string
     */
    public function modelClass(): string
    {
        $repository = class_basename(static::class);
        $model = Str::of($repository)->beforeLast('Repository')->toString();

        $modelClass = app()->getNamespace().'Models\\'.$model;

        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException(sprintf('Model class [%s] does not exist.', $modelClass));
        }

        return $modelClass;
    }

    /**
     * @return Builder<TModel>
     */
    final public function query(?Closure $callable = null): Builder
    {
        $query = $this->model->newQuery();

        if ($callable instanceof Closure) {
            $callable($query);
        }

        return $query;
    }

    /**
     * @param  array<int, string>  $columns
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->query()->get($columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @return TModel|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->model->query()->find($id, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @return TModel
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->model->query()->findOrFail($id, $columns);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function store(array $data): Model
    {
        return $this->model->query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): bool
    {
        return $this->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    /**
     * @param  array<int, string>  $columns
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->query()->paginate($perPage, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @return TModel|null
     */
    public function findBySlug(string $slug, array $columns = ['*']): ?Model
    {
        return $this->model->query()->where('slug', $slug)->first($columns);
    }

    /**
     * @param  string|array<string, mixed>  $relations
     * @return Builder<TModel>
     */
    public function with($relations): Builder
    {
        return $this->model->query()->with($relations);
    }

    /**
     * @return TModel
     */
    private function resolveModel(): Model
    {
        $modelClass = $this->modelClass();

        /** @var class-string<TModel> $modelClass */
        return new $modelClass;
    }
}

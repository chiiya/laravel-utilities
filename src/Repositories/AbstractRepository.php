<?php declare(strict_types=1);

namespace Chiiya\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
abstract class AbstractRepository
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected string $model;
    protected Model $instance;
    protected string $orderBy = 'id';
    protected string $orderDirection = 'ASC';

    public function __construct()
    {
        $this->instance = new $this->model;
    }

    /**
     * Fetch an entity by its id.
     *
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function get(int|string $id, array $parameters = []): Model
    {
        $query = $this->newQuery()->where($this->instance->getKeyName(), $id);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->firstOrFail();
    }

    /**
     * Find an entity by supplied parameters.
     *
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function find(array $parameters = []): Model
    {
        $query = $this->newQuery()->orderBy($this->orderBy, $this->orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->firstOrFail();
    }

    /**
     * Get a list of all entities.
     *
     * @return Collection<int, TModel>
     */
    public function index(array $parameters = []): Collection
    {
        $query = $this->newQuery()->orderBy($this->orderBy, $this->orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->get();
    }

    /**
     * Search entities.
     *
     * @return Collection<int, TModel>|LengthAwarePaginator
     */
    public function search(?string $search, array $parameters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()->orderBy($this->orderBy, $this->orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        $query = $query->where(function (Builder $builder) use ($search): void {
            foreach ($this->searchableFields() as $field) {
                if (is_array($field)) {
                    $builder->orWhereRaw('CONCAT(' . implode(", ' ', ", $field) . ') LIKE ?', ['%' . $search . '%']);
                } else {
                    $builder->orWhere($field, 'LIKE', '%' . $search . '%');
                }
            }
        });

        return $query->paginate();
    }

    /**
     * Create a new entity instance and store it in database.
     *
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        return $this->newQuery()->create($attributes);
    }

    /**
     * Delete an entity from database.
     *
     * @param TModel $model
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * Update an existing entity in database.
     *
     * @param TModel $model
     */
    public function update(Model $model, array $attributes): void
    {
        $model->fill($attributes);
        $model->save();
    }

    /**
     * Create a new entity instance without storing it in database.
     *
     * @return TModel
     */
    public function newInstance(array $attributes = []): Model
    {
        return $this->instance->newInstance($attributes);
    }

    /**
     * Count number of entities in database.
     */
    public function count(array $parameters = []): int
    {
        $query = $this->newQuery();
        $query = $this->applyFilters($query, $parameters);

        return $query->count();
    }

    /**
     * @return Builder<TModel>|TModel
     *
     * @phpstan-return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return $this->instance->newQuery();
    }

    /**
     * Apply eager loads.
     *
     * @phpstan-param Builder<TModel> $builder
     *
     * @phpstan-return Builder<TModel>
     */
    protected function applyEagerLoads(Builder $builder, array $parameters): Builder
    {
        if (isset($parameters['with'])) {
            $builder->with($parameters['with']);
        }

        if (isset($parameters['withCount'])) {
            $builder->withCount($parameters['withCount']);
        }

        return $builder;
    }

    /**
     * List of fields that should be searchable.
     */
    protected function searchableFields(): array
    {
        return [];
    }

    /**
     * Apply custom query filters.
     *
     * @phpstan-param Builder<TModel> $builder
     *
     * @phpstan-return Builder<TModel>
     */
    protected function applyFilters(Builder $builder, array $parameters): Builder
    {
        return $builder;
    }
}

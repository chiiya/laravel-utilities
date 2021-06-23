<?php

namespace Chiiya\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class AbstractRepository
{
    /** @var Model */
    protected $model;
    /** @var string */
    protected static $orderBy = 'id';
    /** @var string */
    protected static $orderDirection = 'ASC';

    /**
     * Fetch an entity by its id.
     *
     * @param int|string $id
     *
     * @throws ModelNotFoundException
     */
    public function get($id, array $parameters = []): Model
    {
        $query = $this->model->newQuery()->where($this->model->getKeyName(), $id);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->firstOrFail();
    }

    /**
     * Find an entity by supplied parameters.
     *
     * @throws ModelNotFoundException
     */
    public function find(array $parameters = []): Model
    {
        $query = $this->model->newQuery()->orderBy(static::$orderBy, static::$orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->firstOrFail();
    }

    /**
     * Get a list of all entities.
     *
     * @return Model[]|Collection
     */
    public function index(array $parameters = [])
    {
        $query = $this->model->newQuery()->orderBy(static::$orderBy, static::$orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        return $query->get();
    }

    /**
     * Search entities.
     *
     * @return Model[]|Collection|LengthAwarePaginator
     */
    public function search(?string $search, array $parameters = [])
    {
        $query = $this->model->newQuery()->orderBy(static::$orderBy, static::$orderDirection);
        $query = $this->applyFilters($query, $parameters);
        $query = $this->applyEagerLoads($query, $parameters);

        $query = $query->where(function (Builder $builder) use ($search) {
            foreach ($this->searchableFields() as $field) {
                if (is_array($field)) {
                    $builder->orWhereRaw('CONCAT('.implode(", ' ', ", $field).') LIKE ?', ['%'.$search.'%']);
                } else {
                    $builder->orWhere($field, 'LIKE', '%'.$search.'%');
                }
            }
        });

        return $query->paginate();
    }

    /**
     * Create a new entity instance and store it in database.
     */
    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * Delete an entity from database.
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * Update an existing entity in database.
     */
    public function update(Model $model, array $attributes): void
    {
        $model->fill($attributes);
        $model->save();
    }

    /**
     * Create a new entity instance without storing it in database.
     */
    public function newInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Count number of entities in database.
     */
    public function count(array $parameters = []): int
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $parameters);

        return $query->count();
    }

    /**
     * Apply eager loads.
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
     */
    abstract protected function applyFilters(Builder $builder, array $parameters): Builder;
}

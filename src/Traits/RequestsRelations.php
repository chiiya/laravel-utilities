<?php

namespace Chiiya\Common\Traits;

trait RequestsRelations
{
    /** @var array|null */
    protected $requestedRelationsCache;

    /**
     * Get all the relationships that the client wants.
     */
    public function requestedRelations(): array
    {
        if ($this->requestedRelationsCache === null) {
            $relations = $this->has('include') ? explode(',', $this->get('include', '')) : [];
            $this->requestedRelationsCache = array_intersect($this->relations, $relations);
        }

        return $this->requestedRelationsCache;
    }

    /**
     * Does the client request any relations?
     */
    public function requestsRelations(): bool
    {
        return count($this->requestedRelations()) > 0;
    }

    /**
     * Does the client request a specific relation?
     */
    public function requestsRelation(string $relation): bool
    {
        return in_array($relation, $this->requestedRelations(), true);
    }

    /**
     * Does the client request at least one of the specified relations?
     */
    public function requestsOneOf(array $relations): bool
    {
        return count(array_intersect($this->requestedRelations(), $relations)) > 0;
    }
}

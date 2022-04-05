<?php declare(strict_types=1);

namespace Chiiya\Common\Presenter;

/**
 * @template TEntity of object
 */
abstract class Presenter
{
    /**
     * @param TEntity $entity
     */
    public function __construct(
        protected object $entity,
    ) {}

    /**
     * Allow for property-style retrieval.
     */
    public function __get(string $property): mixed
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->entity->{$property};
    }
}

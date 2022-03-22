<?php declare(strict_types=1);

namespace Chiiya\Common\Presenter;

use Illuminate\Database\Eloquent\Model;

abstract class Presenter
{
    public function __construct(
        protected Model $entity,
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

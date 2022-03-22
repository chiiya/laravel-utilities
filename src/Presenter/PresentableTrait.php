<?php declare(strict_types=1);

namespace Chiiya\Common\Presenter;

/**
 * @template TPresenter of \Chiiya\Common\Presenter\Presenter
 *
 * @property class-string<TPresenter> $presenter
 */
trait PresentableTrait
{
    /**
     * Presenter instance.
     *
     * @var TPresenter|null
     */
    protected ?Presenter $presenterInstance = null;

    /**
     * Prepare a new or cached presenter instance.
     *
     * @return TPresenter
     */
    public function present(): Presenter
    {
        if ($this->presenterInstance !== null) {
            return $this->presenterInstance;
        }

        return $this->presenterInstance = new $this->presenter($this);
    }
}

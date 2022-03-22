<?php declare(strict_types=1);

namespace Chiiya\Common\Presenter;

/**
 * @template TPresenter of \Chiiya\Common\Presenter\Presenter
 */
trait PresentableTrait
{
    /**
     * The name of the presenter class.
     *
     * @var class-string<TPresenter>
     */
    protected string $presenter;

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

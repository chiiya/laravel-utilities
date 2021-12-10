<?php

namespace Chiiya\Common\Mail;

/**
 * @property array $callbacks
 */
trait SetsReturnPath
{
    /**
     * Set the return path for the message.
     *
     * @return $this
     */
    public function returnPath(string $returnPath): self
    {
        $this->callbacks[] = function ($message) use ($returnPath) {
            $message->returnPath($returnPath);
        };

        return $this;
    }
}

<?php

namespace Chiiya\Common\Mail;

use Symfony\Component\Mime\Address;

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
    public function sender(string $address, ?string $name = null): self
    {
        $this->callbacks[] = function ($message) use ($address, $name) {
            return $message->sender(new Address($address, (string) $name));
        };

        return $this;
    }
}

<?php declare(strict_types=1);

namespace Chiiya\Common\Mail;

use Symfony\Component\Mime\Address;

/**
 * @property array $callbacks
 */
trait SetsSender
{
    /**
     * Set the sender (return path) for the message.
     */
    public function sender(string $address, ?string $name = null): static
    {
        $this->callbacks[] = fn ($message) => $message->sender(new Address($address, (string) $name));

        return $this;
    }
}

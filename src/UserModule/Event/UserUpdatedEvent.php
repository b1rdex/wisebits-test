<?php

namespace App\UserModule\Event;

readonly class UserUpdatedEvent
{
    /**
     * @param array<string, mixed> $changes
     */
    public function __construct(public int $id, public array $changes)
    {
    }
}

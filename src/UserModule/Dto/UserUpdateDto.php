<?php

namespace App\UserModule\Dto;

class UserUpdateDto
{
    /**
     * @var array<string, mixed>
     */
    private array $changes = [];

    public function withName(string $value): static
    {
        $clone = clone $this;
        $clone->changes['name'] = $value;

        return $clone;
    }

    public function withEmail(string $value): static
    {
        $clone = clone $this;
        $clone->changes['email'] = $value;

        return $clone;
    }

    public function withDeleted(?\DateTimeImmutable $value): static
    {
        $clone = clone $this;
        $clone->changes['deleted'] = $value;

        return $clone;
    }

    public function withNotes(?string $value): static
    {
        $clone = clone $this;
        $clone->changes['notes'] = $value;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}

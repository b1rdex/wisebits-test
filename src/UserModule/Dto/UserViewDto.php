<?php

namespace App\UserModule\Dto;

readonly class UserViewDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public \DateTimeImmutable $created,
        public ?\DateTimeImmutable $deleted,
        public ?string $notes,
    ) {
    }
}

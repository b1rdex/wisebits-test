<?php

namespace App\UserModule\Dto;

readonly class UserCreateDto
{
    public function __construct(
        public string $name,
        public string $email,
        public \DateTimeImmutable $created,
        public ?\DateTimeImmutable $deleted,
        public ?string $notes,
    ) {
    }
}

<?php

namespace App\UserModule\Exception;

class ValidationException extends \RuntimeException implements UserRepositoryException
{
    public function __construct(
        public readonly string $field,
        public readonly string $validator,
        public readonly mixed $value,
    ) {
        $message = sprintf('Validation of field "%s" failed: %s', $field, $validator);

        parent::__construct($message);
    }
}

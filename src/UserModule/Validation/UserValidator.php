<?php

namespace App\UserModule\Validation;

use App\UserModule\Dto\UserViewDto;
use App\UserModule\Exception\ValidationException;

readonly class UserValidator
{
    public function __construct(private ValidatorsCollection $validatorsCollection)
    {
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function validate(?UserViewDto $before, array $changes): void
    {
        $validators = $this->validatorsCollection->getValidators();

        foreach ($changes as $field => $value) {
            if (!isset($validators[$field])) {
                continue;
            }

            foreach ($validators[$field] as $name => $validator) {
                if (!$validator($value, $before, $changes)) {
                    throw new ValidationException($field, $name, $value);
                }
            }
        }
    }
}

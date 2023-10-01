<?php

namespace App\UserModule\Validation;

use App\UserModule\Dto\Criteria;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\UserRepositoryRead;

/**
 * @phpstan-type TValidator = callable(mixed, ?UserViewDto, array<string, mixed>): bool
 */
readonly class ValidatorsCollection
{
    public function __construct(private UserRepositoryRead $userReadRepository)
    {
    }

    /**
     * @return array<string, array<string, TValidator>>
     */
    public function getValidators(): array
    {
        return [
            'name' => $this->getNameValidators(),
            'email' => $this->getEmailValidators(),
            'deleted' => $this->getDeletedValidators(),
        ];
    }

    /**
     * @return array<string, TValidator>
     */
    private function getNameValidators(): array
    {
        return [
            'Must consist of a-z and 0-9 only' => $this->createStringValidator(function (string $value) {
                return ctype_alnum($value);
            }),
            'Must be more than 8 characters' => $this->createStringValidator(function (string $value) {
                return strlen($value) >= 8;
            }),
            'Must not use banned words' => $this->createStringValidator(function (string $value) {
                // todo: тут можно подключить внешний сервис и проверять через него
                return !in_array($value, ['some', 'banned', 'words'], true);
            }),
            'Must be unique' => $this->createUniqueNameValidator(),
        ];
    }

    /**
     * @return array<string, TValidator>
     */
    private function getEmailValidators(): array
    {
        return [
            'Must be valid email address' => $this->createStringValidator(function (string $value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            }),
            'Must not use banned domains' => $this->createStringValidator(function (string $value) {
                // todo: тут можно подключить внешний сервис и проверять через него
                return !in_array($value, ['some', 'banned', 'domains'], true);
            }),
            'Must be unique' => $this->createUniqueEmailValidator(),
        ];
    }

    /**
     * @return array<string, TValidator>
     */
    private function getDeletedValidators(): array
    {
        return [
            'Must be greater than created' => function (mixed $value, ?UserViewDto $before, array $changes) {
                if (null !== $value && !($value instanceof \DateTimeImmutable)) {
                    throw new \LogicException('Should not happen');
                }

                if (null === $value) {
                    return true;
                }

                if (null !== $before) {
                    return $value > $before->created;
                }

                if (!isset($changes['created']) || !($changes['created'] instanceof \DateTimeImmutable)) {
                    throw new \LogicException('Should not happen');
                }

                return $value > $changes['created'];
            },
        ];
    }

    /**
     * @param callable(string):bool $callback
     *
     * @return callable(mixed):bool
     */
    private function createStringValidator(callable $callback): callable
    {
        return static function (mixed $value) use ($callback) {
            if (!is_string($value)) {
                throw new \LogicException('Should not happen');
            }

            return $callback($value);
        };
    }

    /**
     * @return callable(mixed, ?UserViewDto):bool
     */
    private function createUniqueNameValidator(): callable
    {
        return function (mixed $value, ?UserViewDto $before) {
            if (!is_string($value)) {
                throw new \LogicException('Should not happen');
            }

            $criteria = (new Criteria())->withNameEq($value);
            $found = $this->userReadRepository->findBy($criteria);
            if (null !== $before) {
                $found = array_filter($found, static fn (UserViewDto $item) => $item->id !== $before->id);
            }

            return count($found) === 0;
        };
    }

    /**
     * @return callable(mixed, ?UserViewDto):bool
     */
    private function createUniqueEmailValidator(): callable
    {
        return function (mixed $value, ?UserViewDto $before) {
            if (!is_string($value)) {
                throw new \LogicException('Should not happen');
            }

            $criteria = (new Criteria())->withEmailEq($value);
            $found = $this->userReadRepository->findBy($criteria);
            if (null !== $before) {
                $found = array_filter($found, static fn (UserViewDto $item) => $item->id !== $before->id);
            }

            return count($found) === 0;
        };
    }
}

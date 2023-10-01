<?php

namespace App\UserModule\Validation;

use App\UserModule\Dto\Criteria;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\UserRepositoryRead;

readonly class ValidatorsCollection
{
    public function __construct(private UserRepositoryRead $userReadRepository)
    {
    }

    /**
     * @return array<string, array<string, callable(mixed, ?UserViewDto, array<string, mixed>): bool>>
     */
    public function getValidators(): array
    {
        return [
            'name' => [
                'Must consist of a-z and 0-9 only' => function (mixed $value) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    return ctype_alnum($value);
                },
                'Must be more than 8 characters' => function (mixed $value) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    return strlen($value) >= 8;
                },
                'Must not use banned words' => function (mixed $value) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    // todo: this can be implemented with an external service
                    return !in_array($value, ['some', 'banned', 'words'], true);
                },
                'Must be unique' => function (mixed $value, ?UserViewDto $before) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    $criteria = (new Criteria())->withNameEq($value);
                    $found = $this->userReadRepository->findBy($criteria);
                    if (null !== $before) {
                        $found = array_filter($found, static fn (UserViewDto $item) => $item->id !== $before->id);
                    }

                    return count($found) === 0;
                },
            ],
            'email' => [
                'Must be valid email address' => function (mixed $value) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                },
                'Must not use banned domains' => function (mixed $value) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    // todo: this can be implemented with an external service
                    return !in_array($value, ['some', 'banned', 'domains'], true);
                },
                'Must be unique' => function (mixed $value, ?UserViewDto $before) {
                    if (!is_string($value)) {
                        throw new \LogicException('Should not happen');
                    }

                    $criteria = (new Criteria())->withEmailEq($value);
                    $found = $this->userReadRepository->findBy($criteria);
                    if (null !== $before) {
                        $found = array_filter($found, static fn (UserViewDto $item) => $item->id !== $before->id);
                    }

                    return count($found) === 0;
                },
            ],
            'deleted' => [
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
            ],
        ];
    }
}

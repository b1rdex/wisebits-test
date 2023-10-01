<?php

namespace App\UserModule\Impl\InMemory;

use App\UserModule\Dto\Criteria;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\Exception\UserNotFoundException;
use App\UserModule\UserRepositoryRead as BaseUserRepositoryRead;

readonly class UserRepositoryRead implements BaseUserRepositoryRead
{
    public function __construct(
        private Memory $memory,
        private Hydrator $hydrator,
    ) {
    }

    public function find(int $id): UserViewDto
    {
        $item = $this->memory->get($id);
        if (null === $item) {
            throw new UserNotFoundException(sprintf('User with identifier "%s" not found', $id));
        }

        return $this->hydrator->hydrate($id, $item);
    }

    public function findBy(Criteria $criteria): array
    {
        $params = $criteria->getParams();
        if (count($params) === 0) {
            return [];
        }

        $result = [];
        foreach ($this->memory->getItems() as $id => $item) {
            foreach ($params as $param => $value) {
                if (match ($param) {
                    'name-eq' => $item['name'] === $value,
                    'email-eq' => $item['email'] === $value,
                    // default case is handled by phpstan
                }) {
                    $result[] = $this->hydrator->hydrate($id, $item);
                }
            }
        }

        return $result;
    }
}

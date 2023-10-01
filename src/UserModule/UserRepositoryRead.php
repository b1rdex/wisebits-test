<?php

namespace App\UserModule;

use App\UserModule\Dto\Criteria;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\Exception\UserNotFoundException;

interface UserRepositoryRead
{
    /**
     * @throws UserNotFoundException
     */
    public function find(int $id): UserViewDto;

    /**
     * @return list<UserViewDto>
     */
    public function findBy(Criteria $criteria): array;
}

<?php

namespace App\UserModule;

use App\UserModule\Dto\UserCreateDto;
use App\UserModule\Dto\UserUpdateDto;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\Exception\PersistException;
use App\UserModule\Exception\UserNotFoundException;
use App\UserModule\Exception\ValidationException;

interface UserRepositoryWrite
{
    /**
     * @throws ValidationException
     * @throws PersistException
     */
    public function create(UserCreateDto $data): UserViewDto;

    /**
     * @throws UserNotFoundException
     * @throws ValidationException
     * @throws PersistException
     */
    public function update(int $id, UserUpdateDto $data): UserViewDto;
}

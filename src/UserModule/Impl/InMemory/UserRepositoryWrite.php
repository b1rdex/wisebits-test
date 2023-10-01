<?php

namespace App\UserModule\Impl\InMemory;

use App\UserModule\Dto\UserCreateDto;
use App\UserModule\Dto\UserUpdateDto;
use App\UserModule\Dto\UserViewDto;
use App\UserModule\Event\UserUpdatedEvent;
use App\UserModule\Exception\UserNotFoundException;
use App\UserModule\UserRepositoryWrite as BaseUserRepositoryWrite;
use App\UserModule\Validation\UserValidator;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type TRecord from Memory
 */
readonly class UserRepositoryWrite implements BaseUserRepositoryWrite
{
    public function __construct(
        private Memory $memory,
        private EventDispatcherInterface $eventDispatcher,
        private UserValidator $validator,
        private Hydrator $hydrator,
    ) {
    }

    public function create(UserCreateDto $data): UserViewDto
    {
        $item = [
            'name' => $data->name,
            'email' => $data->email,
            'created' => $data->created,
            'deleted' => $data->deleted,
            'notes' => $data->notes,
        ];

        $this->validator->validate(null, $item);

        $id = $this->memory->getNextId();
        $this->memory->set($id, $item);

        return $this->hydrator->hydrate($id, $item);
    }

    public function update(int $id, UserUpdateDto $data): UserViewDto
    {
        $item = $this->memory->get($id);
        if (null === $item) {
            throw new UserNotFoundException(sprintf('User with identifier "%s" not found', $id));
        }

        $changes = $data->getChanges();
        if (count($changes) === 0) {
            return $this->hydrator->hydrate($id, $item);
        }

        $before = $this->hydrator->hydrate($id, $item);
        $this->validator->validate($before, $changes);

        foreach ($changes as $name => $value) {
            $item[$name] = $value;
        }
        /** @var TRecord $item */
        $this->memory->set($id, $item);

        // событие для журналирования изменений пользователей
        $this->eventDispatcher->dispatch(new UserUpdatedEvent($id, $changes));

        return $this->hydrator->hydrate($id, $item);
    }
}

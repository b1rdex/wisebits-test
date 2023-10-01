<?php

namespace App\UserModule\Impl\InMemory;

use App\UserModule\Dto\UserViewDto;

/**
 * @phpstan-import-type TRecord from Memory
 */
class Hydrator
{
    /**
     * @param TRecord $item
     */
    public function hydrate(int $id, array $item): UserViewDto
    {
        return new UserViewDto($id, $item['name'], $item['email'], $item['created'], $item['deleted'], $item['notes']);
    }
}

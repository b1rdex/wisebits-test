<?php

namespace App\UserModule\Impl\InMemory;

/**
 * @phpstan-type TRecord = array{name: string, email: string, created: \DateTimeImmutable, deleted: \DateTimeImmutable|null, notes: string|null}
 */
class Memory
{
    public function __construct(
        /** @var array<int, TRecord> */
        private array $items = []
    ) {
    }

    /**
     * @return array<int, TRecord>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return TRecord|null
     */
    public function get(int $id): ?array
    {
        return $this->items[$id] ?? null;
    }

    public function getNextId(): int
    {
        if (count($this->items) === 0) {
            return 1;
        }

        return max(array_keys($this->items)) + 1;
    }

    /**
     * @param TRecord $item
     */
    public function set(int $id, array $item): void
    {
        $this->items[$id] = $item;
    }
}

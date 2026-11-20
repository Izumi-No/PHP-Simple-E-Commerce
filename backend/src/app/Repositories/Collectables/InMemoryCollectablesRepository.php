<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\Collectables;

use Izumi\Backend\app\Models\Collectable\Collectable;

final class InMemoryCollectablesRepository implements ICollectablesRepository
{
    /** @var array<string, Collectable> */
    private array $collectables = [];

    public function save(Collectable $data): Collectable
    {
        $this->collectables[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?Collectable
    {
        return $this->collectables[$id] ?? null;
    }

    /** @return array<Collectable> */
    public function findAll(): array
    {
        return array_values($this->collectables);
    }

    /** @return array<Collectable> */
    public function findByShop(string $shopId): array
    {
        return array_values(array_filter($this->collectables, fn(Collectable $c) => $c->shop === $shopId));
    }

    public function update(string $id, array $data): void
    {
        if (!isset($this->collectables[$id])) {
            throw new \Exception('Collectable not found');
        }
        $collectable = $this->collectables[$id];
        $errors = $collectable->update($data);
        if ($errors !== []) {
            throw new \Exception('Invalid update data');
        }
        $this->collectables[$id] = $collectable;
    }

    public function delete(string $id): void
    {
        unset($this->collectables[$id]);
    }
}

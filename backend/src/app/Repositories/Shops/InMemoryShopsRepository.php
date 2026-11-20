<?php

namespace Izumi\Backend\app\Repositories\Shops;

use Izumi\Backend\app\Models\Shop\Shop;

final class InMemoryShopsRepository implements IShopsRepository
{
    /** @var array<string, Shop> */
    private array $shops = [];

    public function save(Shop $data): Shop
    {
        $this->shops[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?Shop
    {
        return $this->shops[$id] ?? null;
    }

    /** @return array<Shop> */
    public function findAll(): array
    {
        return array_values($this->shops);
    }

    public function findByOwner(string $ownerId): ?Shop
    {
        foreach ($this->shops as $shop) {
            if ($shop->owner === $ownerId) {
                return $shop;
            }
        }
        return null;
    }

    public function update(string $id, array $data): void
    {
        if (!isset($this->shops[$id])) {
            throw new \Exception('Shop not found');
        }
        $shop = $this->shops[$id];
        $errors = $shop->update($data);
        if ($errors !== []) {
            throw new \Exception('Invalid update data');
        }
        $this->shops[$id] = $shop;
    }

    public function delete(string $id): void
    {
        unset($this->shops[$id]);
    }
}

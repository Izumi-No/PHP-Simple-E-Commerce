<?php

namespace Izumi\Backend\app\Repositories\Shops;

use Izumi\Backend\app\Models\Shop\Shop;

interface IShopsRepository
{
    public function save(Shop $data): Shop;

    public function findById(string $id): ?Shop;

    /** @return array<Shop> */
    public function findAll(): array;

    public function findByOwner(string $ownerId): ?Shop;

    public function update(string $id, array $data): void;

    public function delete(string $id): void;
}

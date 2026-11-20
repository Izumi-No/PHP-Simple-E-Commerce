<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\Collectables;

use Izumi\Backend\app\Models\Collectable\Collectable;

interface ICollectablesRepository
{
    public function save(Collectable $data): Collectable;

    public function findById(string $id): ?Collectable;

    /** @return array<Collectable> */
    public function findAll(): array;

    /** @return array<Collectable> */
    public function findByShop(string $shopId): array;

    public function update(string $id, array $data): void;

    public function delete(string $id): void;
}

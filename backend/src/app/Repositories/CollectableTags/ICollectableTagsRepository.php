<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\CollectableTags;

use Izumi\Backend\app\Models\CollectableTag\CollectableTag;

interface ICollectableTagsRepository
{
    public function save(CollectableTag $data): CollectableTag;

    /** @return array<CollectableTag> */
    public function findByCollectable(string $collectableId): array;

    public function findById(string $id): ?CollectableTag;

    public function delete(string $id): void;
}

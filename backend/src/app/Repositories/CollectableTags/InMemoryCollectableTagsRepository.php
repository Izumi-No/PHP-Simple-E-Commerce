<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\CollectableTags;

use Izumi\Backend\app\Models\CollectableTag\CollectableTag;

final class InMemoryCollectableTagsRepository implements ICollectableTagsRepository
{
    /** @var array<string, CollectableTag> */
    private array $associations = [];

    public function save(CollectableTag $data): CollectableTag
    {
        $this->associations[$data->id] = $data;
        return $data;
    }

    /** @return array<CollectableTag> */
    public function findByCollectable(string $collectableId): array
    {
        return array_values(array_filter(
            $this->associations,
            fn(CollectableTag $ct) => $ct->collectable === $collectableId,
        ));
    }

    public function findById(string $id): ?CollectableTag
    {
        return $this->associations[$id] ?? null;
    }

    public function delete(string $id): void
    {
        unset($this->associations[$id]);
    }
}

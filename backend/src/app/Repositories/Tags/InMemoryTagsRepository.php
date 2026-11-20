<?php

namespace Izumi\Backend\app\Repositories\Tags;

use Izumi\Backend\app\Models\Tag\Tag;

final class InMemoryTagsRepository implements ITagsRepository
{
    /** @var array<string, Tag> */
    private array $tags = [];

    public function save(Tag $data): Tag
    {
        $this->tags[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?Tag
    {
        return $this->tags[$id] ?? null;
    }

    /** @return array<Tag> */
    public function findByShop(string $shopId): array
    {
        return array_values(array_filter($this->tags, fn(Tag $tag) => $tag->shop === $shopId));
    }

    public function delete(string $id): void
    {
        unset($this->tags[$id]);
    }
}

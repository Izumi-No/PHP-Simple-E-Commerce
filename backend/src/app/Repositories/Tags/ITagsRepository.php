<?php

namespace Izumi\Backend\app\Repositories\Tags;

use Izumi\Backend\app\Models\Tag\Tag;

interface ITagsRepository
{
    public function save(Tag $data): Tag;

    public function findById(string $id): ?Tag;

    /** @return array<Tag> */
    public function findByShop(string $shopId): array;

    public function delete(string $id): void;
}

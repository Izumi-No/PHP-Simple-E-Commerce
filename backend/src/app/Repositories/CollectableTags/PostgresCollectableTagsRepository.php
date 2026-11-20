<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\CollectableTags;

use Izumi\Backend\app\Models\CollectableTag\CollectableTag;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresCollectableTagsRepository implements ICollectableTagsRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(CollectableTag $data): CollectableTag
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO collectable_tags (collectable_id, tag_id) VALUES (:collectable_id, :tag_id) RETURNING *',
            );
            $stmt->execute([
                ':collectable_id' => $data->collectable,
                ':tag_id' => $data->tag,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('CollectableTag was not returned after creation.');
            }
            return $this->rowToCollectableTag($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create collectable tag: ' . $e->getMessage(), previous: $e);
        }
    }

    /** @return array<CollectableTag> */
    public function findByCollectable(string $collectableId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM collectable_tags WHERE collectable_id = :collectable_id AND deleted_at IS NULL',
        );
        $stmt->execute([':collectable_id' => $collectableId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): CollectableTag => $this->rowToCollectableTag($row), $rows);
    }

    public function findById(string $id): ?CollectableTag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM collectable_tags WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $this->rowToCollectableTag($row);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE collectable_tags SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
    }

    private function rowToCollectableTag(array $row): CollectableTag
    {
        return CollectableTag::fromArray([
            'id' => $row['id'],
            'collectable' => $row['collectable_id'],
            'tag' => $row['tag_id'],
        ]);
    }
}

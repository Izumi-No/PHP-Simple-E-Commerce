<?php

namespace Izumi\Backend\app\Repositories\Tags;

use Izumi\Backend\app\Models\Tag\Tag;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresTagsRepository implements ITagsRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(Tag $data): Tag
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO tags (name, shop_id) VALUES (:name, :shop_id) RETURNING *');
            $stmt->execute([
                ':name' => $data->name,
                ':shop_id' => $data->shop,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('Tag was not returned after creation.');
            }
            return Tag::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create tag: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $this->rowToTag($row);
    }

    /** @return array<Tag> */
    public function findByShop(string $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM tags WHERE shop_id = :shop_id AND deleted_at IS NULL ORDER BY name ASC',
        );
        $stmt->execute([':shop_id' => $shopId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): Tag => $this->rowToTag($row), $rows);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tags SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }

    private function rowToTag(array $row): Tag
    {
        return Tag::fromArray([
            'id' => $row['id'],
            'name' => $row['name'],
            'shop' => $row['shop_id'],
        ]);
    }
}

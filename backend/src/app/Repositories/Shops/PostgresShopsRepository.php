<?php

namespace Izumi\Backend\app\Repositories\Shops;

use Izumi\Backend\app\Models\Shop\Shop;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresShopsRepository implements IShopsRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(Shop $data): Shop
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO shops (name, banner, owner) VALUES (:name, :banner, :owner) RETURNING *',
            );
            $stmt->execute([
                ':name' => $data->name,
                ':banner' => $data->banner,
                ':owner' => $data->owner,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('Shop was not returned after creation.');
            }
            return Shop::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create shop: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?Shop
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shops WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Shop::fromArray($row);
    }

    /** @return array<Shop> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM shops WHERE deleted_at IS NULL ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): Shop => Shop::fromArray($row), $rows);
    }

    public function findByOwner(string $ownerId): ?Shop
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shops WHERE owner = :owner AND deleted_at IS NULL');
        $stmt->execute([':owner' => $ownerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Shop::fromArray($row);
    }

    public function update(string $id, array $data): void
    {
        $allowedFields = ['name', 'banner'];
        $setClause = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields, true)) {
                continue;
            }
            $setClause[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        if ($setClause === []) {
            throw new \InvalidArgumentException('No valid fields to update.');
        }
        $setClause[] = 'updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->pdo->prepare(
            'UPDATE shops SET ' . implode(', ', $setClause) . ' WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute($params);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shops SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }
}

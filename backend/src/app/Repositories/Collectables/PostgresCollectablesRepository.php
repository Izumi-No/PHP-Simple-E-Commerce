<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Repositories\Collectables;

use Izumi\Backend\app\Models\Collectable\Collectable;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresCollectablesRepository implements ICollectablesRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(Collectable $data): Collectable
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO collectables (name, description, images, price, shop_id)
                 VALUES (:name, :description, :images, :price, :shop_id) RETURNING *',
            );
            $stmt->execute([
                ':name' => $data->name,
                ':description' => $data->description,
                ':images' => json_encode($data->images, JSON_THROW_ON_ERROR),
                ':price' => $data->price,
                ':shop_id' => $data->shop,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('Collectable was not returned after creation.');
            }
            return $this->rowToCollectable($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create collectable: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?Collectable
    {
        $stmt = $this->pdo->prepare('SELECT * FROM collectables WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $this->rowToCollectable($row);
    }

    /** @return array<Collectable> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM collectables WHERE deleted_at IS NULL ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): Collectable => $this->rowToCollectable($row), $rows);
    }

    /** @return array<Collectable> */
    public function findByShop(string $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM collectables WHERE shop_id = :shop_id AND deleted_at IS NULL ORDER BY created_at DESC',
        );
        $stmt->execute([':shop_id' => $shopId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): Collectable => $this->rowToCollectable($row), $rows);
    }

    public function update(string $id, array $data): void
    {
        $allowedFields = ['name', 'description', 'images', 'price'];
        $setClause = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields, true)) {
                continue;
            }
            $column = match ($key) {
                'images' => 'images',
                default => $key,
            };
            $paramName = ":$key";
            if ($key === 'images') {
                $params[$paramName] = json_encode($value, JSON_THROW_ON_ERROR);
            } else {
                $params[$paramName] = $value;
            }
            $setClause[] = "$column = $paramName";
        }
        if ($setClause === []) {
            throw new \InvalidArgumentException('No valid fields to update.');
        }
        $setClause[] = 'updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->pdo->prepare(
            'UPDATE collectables SET ' . implode(', ', $setClause) . ' WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute($params);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE collectables SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
    }

    private function rowToCollectable(array $row): Collectable
    {
        $images = $row['images'];
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        return Collectable::fromArray([
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'images' => $images,
            'price' => $row['price'],
            'shop' => $row['shop_id'],
        ]);
    }
}

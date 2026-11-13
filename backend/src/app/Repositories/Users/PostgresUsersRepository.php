<?php

namespace Izumi\Backend\app\Repositories\Users;

use Izumi\Backend\app\Models\Auth\User;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresUsersRepository implements IUsersRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(User $data): User
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO users (name, nickname) VALUES (:name, :nickname) RETURNING *');
            $stmt->execute([
                ':name' => $data->name,
                ':nickname' => $data->nickname,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('User was not returned after creation.');
            }
            return User::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create user: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return User::fromArray($row);
    }

    /** @return array<User> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): User => User::fromArray($row), $rows);
    }

    public function update(string $id, array $data): void
    {
        $allowedFields = ['name', 'nickname'];
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
            'UPDATE users SET ' . implode(', ', $setClause) . ' WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute($params);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }
}

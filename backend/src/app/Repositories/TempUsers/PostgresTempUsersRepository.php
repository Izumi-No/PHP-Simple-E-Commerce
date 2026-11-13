<?php

namespace Izumi\Backend\app\Repositories\TempUsers;

use Izumi\Backend\app\Models\Auth\TempUser;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresTempUsersRepository implements ITempUsersRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(TempUser $data): TempUser
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO temp_users (email, password_hash, expiration)
                 VALUES (:email, :password_hash, :expiration) RETURNING *');
            $stmt->execute([
                ':email' => $data->email,
                ':password_hash' => $data->password_hash,
                ':expiration' => $data->expiration->format('Y-m-d H:i:s'),
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('TempUser was not returned after creation.');
            }
            return TempUser::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create temp user: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?TempUser
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM temp_users WHERE id = :id AND deleted_at IS NULL AND expiration > CURRENT_TIMESTAMP',
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return TempUser::fromArray($row);
    }

    /** @return array<TempUser> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM temp_users WHERE deleted_at IS NULL ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): TempUser => TempUser::fromArray($row), $rows);
    }

    public function findByEmail(string $email): ?TempUser
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM temp_users WHERE email = :email AND deleted_at IS NULL AND expiration > CURRENT_TIMESTAMP',
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return TempUser::fromArray($row);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE temp_users SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }

    public function cleanupExpired(): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM temp_users WHERE expiration < CURRENT_TIMESTAMP');
        $stmt->execute();
        return $stmt->rowCount();
    }
}

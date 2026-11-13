<?php

namespace Izumi\Backend\app\Repositories\Sessions;

use Izumi\Backend\app\Models\Auth\Session;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresSessionsRepository implements ISessionsRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(Session $data): Session
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO sessions (user_id, expiration) VALUES (:user_id, :expiration) RETURNING *',
            );
            $stmt->execute([
                ':user_id' => $data->user,
                ':expiration' => $data->expiration->format('Y-m-d H:i:s'),
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('Session was not returned after creation.');
            }
            return Session::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create session: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?Session
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM sessions WHERE id = :id AND deleted_at IS NULL AND expiration > CURRENT_TIMESTAMP',
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Session::fromArray($row);
    }

    /** @return array<Session> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM sessions WHERE deleted_at IS NULL AND expiration > CURRENT_TIMESTAMP ORDER BY created_at DESC',
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row): Session => Session::fromArray($row), $rows);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE sessions SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }

    public function cleanupExpired(): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE expiration < CURRENT_TIMESTAMP');
        $stmt->execute();
        return $stmt->rowCount();
    }
}

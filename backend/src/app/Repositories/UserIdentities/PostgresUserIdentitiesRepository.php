<?php

namespace Izumi\Backend\app\Repositories\UserIdentities;

use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\UserIdentity;
use PDO;
use PDOException;
use RuntimeException;

final class PostgresUserIdentitiesRepository implements IUserIdentitiesRepository
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function save(UserIdentity $data): UserIdentity
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO user_identities (user_id, provider, provider_subject, email, password_hash)
                 VALUES (:user_id, :provider, :provider_subject, :email, :password_hash) RETURNING *',
            );
            $stmt->execute([
                ':user_id' => $data->user,
                ':provider' => $data->provider->value,
                ':provider_subject' => $data->provider_subject,
                ':email' => $data->email,
                ':password_hash' => $data->password_hash,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException('Identity was not returned after creation.');
            }
            return UserIdentity::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create user identity: ' . $e->getMessage(), previous: $e);
        }
    }

    public function findById(string $id): ?UserIdentity
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user_identities WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return UserIdentity::fromArray($row);
    }

    public function findByProviderAndSubject(AuthenticationProvider $provider, ?string $providerSubject): ?UserIdentity
    {
        $sql = 'SELECT * FROM user_identities WHERE provider = :provider AND deleted_at IS NULL';
        $params = [':provider' => $provider->value];

        if ($providerSubject !== null) {
            $sql .= ' AND provider_subject = :provider_subject';
            $params[':provider_subject'] = $providerSubject;
        } else {
            $sql .= ' AND provider_subject IS NULL';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return UserIdentity::fromArray($row);
    }

    public function findByEmail(string $email): ?UserIdentity
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM user_identities
             WHERE email = :email AND provider = 'password' AND deleted_at IS NULL",
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return UserIdentity::fromArray($row);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE user_identities SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL',
        );
        $stmt->execute([':id' => $id]);
    }
}

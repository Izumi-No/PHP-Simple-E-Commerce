<?php

namespace Izumi\Backend\app\Models\Auth;

use Izumi\Backend\app\Shared\Model;

final class Session extends Model
{
    private function __construct(
        public string $user,
        public \DateTimeImmutable $expiration,
        public \DateTimeImmutable $created_at,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{user: string, expiration: string, created_at: string} $data
     * @return list<\Izumi\Backend\app\Shared\Errors\Error>|self
     */
    public static function create(array $data): array|self
    {
        return new self(
            $data['user'],
            new \DateTimeImmutable($data['expiration']),
            new \DateTimeImmutable($data['created_at'] ?? date('Y-m-d H:i:s')),
        );
    }

    /**
     * @param array{id: string, user: string, expiration: string, created_at: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['user'],
            new \DateTimeImmutable($data['expiration']),
            new \DateTimeImmutable($data['created_at']),
            $data['id'],
        );
    }

    public function isExpired(): bool
    {
        return $this->expiration < new \DateTimeImmutable();
    }

    /**
     * @return array{ id: string, user: string, expiration: string, created_at: string }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'expiration' => $this->expiration->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

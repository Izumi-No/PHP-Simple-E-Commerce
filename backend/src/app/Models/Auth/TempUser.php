<?php

namespace Izumi\Backend\app\Models\Auth;

use Izumi\Backend\app\Models\Auth\Errors\InvalidEmailError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class TempUser extends Model
{
    private function __construct(
        public string $email,
        public string $password_hash,
        public \DateTimeImmutable $expiration,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{email: string, password_hash: string, expiration: string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $email = $data['email'] ?? '';
        $passwordHash = $data['password_hash'] ?? '';
        $expiration = $data['expiration'] ?? '';

        $errors = self::validate($email);

        if ($errors !== []) {
            return $errors;
        }

        return new self($email, $passwordHash, new \DateTimeImmutable($expiration));
    }

    /**
     * @param array{id: string, email: string, password_hash: string, expiration: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['password_hash'],
            new \DateTimeImmutable($data['expiration']),
            $data['id'],
        );
    }

    public function isExpired(): bool
    {
        return $this->expiration < new \DateTimeImmutable();
    }

    /**
     * @return array{ id: string, email: string, password_hash: string, expiration: string }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
            'expiration' => $this->expiration->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(mixed $email): array
    {
        $errors = [];

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = new InvalidEmailError();
        }

        return $errors;
    }
}

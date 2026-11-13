<?php

namespace Izumi\Backend\app\Models\Auth;

use Izumi\Backend\app\Models\Auth\Errors\InvalidEmailError;
use Izumi\Backend\app\Models\Auth\Errors\InvalidProviderError;
use Izumi\Backend\app\Models\Auth\Errors\MissingPasswordHashError;
use Izumi\Backend\app\Models\Auth\Errors\MissingProviderSubjectError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class UserIdentity extends Model
{
    private function __construct(
        public string $user,
        public AuthenticationProvider $provider,
        public ?string $provider_subject,
        public ?string $email,
        public ?string $password_hash,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{user: string, provider: string, provider_subject?: ?string, email?: ?string, password_hash?: ?string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $user = $data['user'] ?? '';
        $providerRaw = $data['provider'] ?? '';
        $providerSubject = $data['provider_subject'] ?? null;
        $email = $data['email'] ?? null;
        $passwordHash = $data['password_hash'] ?? null;

        $provider = self::resolveProvider($providerRaw);

        if ($provider instanceof InvalidProviderError) {
            return [$provider];
        }

        $errors = self::validate($provider, $providerSubject, $email, $passwordHash);

        if ($errors !== []) {
            return $errors;
        }

        return new self($user, $provider, $providerSubject, $email, $passwordHash);
    }

    /**
     * @param array{id: string, user: string, provider: string, provider_subject?: ?string, email?: ?string, password_hash?: ?string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['user'],
            AuthenticationProvider::from($data['provider']),
            $data['provider_subject'] ?? null,
            $data['email'] ?? null,
            $data['password_hash'] ?? null,
            $data['id'],
        );
    }

    /**
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $providerRaw = $data['provider'] ?? $this->provider->value;
        $provider = self::resolveProvider($providerRaw);

        if ($provider instanceof InvalidProviderError) {
            return [$provider];
        }

        $providerSubject = $data['provider_subject'] ?? $this->provider_subject;
        $email = $data['email'] ?? $this->email;
        $passwordHash = $data['password_hash'] ?? $this->password_hash;

        $errors = self::validate($provider, $providerSubject, $email, $passwordHash);

        if ($errors !== []) {
            return $errors;
        }

        $this->provider = $provider;
        $this->provider_subject = $providerSubject;
        $this->email = $email;
        $this->password_hash = $passwordHash;

        return [];
    }

    /**
     * @return array{
     *   id: string, user: string, provider: string,
     *   provider_subject: ?string, email: ?string, password_hash: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'provider' => $this->provider->value,
            'provider_subject' => $this->provider_subject,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(
        AuthenticationProvider $provider,
        ?string $providerSubject,
        ?string $email,
        ?string $passwordHash,
    ): array {
        $errors = [];

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = new InvalidEmailError();
        }

        match ($provider) {
            AuthenticationProvider::Password => $passwordHash === null && ($errors[] = new MissingPasswordHashError()),
            AuthenticationProvider::OpenIDConnect => (
                $providerSubject === null || $providerSubject === ''
            ) && ($errors[] = new MissingProviderSubjectError()),
        };

        return $errors;
    }

    /**
     * @return AuthenticationProvider|InvalidProviderError
     */
    private static function resolveProvider(string $providerRaw): AuthenticationProvider|InvalidProviderError
    {
        try {
            return AuthenticationProvider::from($providerRaw);
        } catch (\ValueError) {
            return new InvalidProviderError();
        }
    }
}

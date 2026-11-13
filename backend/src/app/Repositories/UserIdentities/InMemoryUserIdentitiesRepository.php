<?php

namespace Izumi\Backend\app\Repositories\UserIdentities;

use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\UserIdentity;

final class InMemoryUserIdentitiesRepository implements IUserIdentitiesRepository
{
    /** @var array<string, UserIdentity> */
    private array $identities = [];

    public function save(UserIdentity $data): UserIdentity
    {
        $this->identities[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?UserIdentity
    {
        return $this->identities[$id] ?? null;
    }

    public function findByProviderAndSubject(AuthenticationProvider $provider, ?string $providerSubject): ?UserIdentity
    {
        foreach ($this->identities as $identity) {
            if ($identity->provider === $provider && $identity->provider_subject === $providerSubject) {
                return $identity;
            }
        }
        return null;
    }

    public function findByEmail(string $email): ?UserIdentity
    {
        foreach ($this->identities as $identity) {
            if ($identity->provider === AuthenticationProvider::Password && $identity->email === $email) {
                return $identity;
            }
        }
        return null;
    }

    public function delete(string $id): void
    {
        unset($this->identities[$id]);
    }
}

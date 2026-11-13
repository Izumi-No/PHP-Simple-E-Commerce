<?php

namespace Izumi\Backend\app\Repositories\UserIdentities;

use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\UserIdentity;

interface IUserIdentitiesRepository
{
    public function save(UserIdentity $data): UserIdentity;

    public function findById(string $id): ?UserIdentity;

    public function findByProviderAndSubject(AuthenticationProvider $provider, ?string $providerSubject): ?UserIdentity;

    public function findByEmail(string $email): ?UserIdentity;

    public function delete(string $id): void;
}

<?php

namespace Izumi\Backend\app\Repositories\TempUsers;

use Izumi\Backend\app\Models\Auth\TempUser;

interface ITempUsersRepository
{
    public function save(TempUser $data): TempUser;

    public function findById(string $id): ?TempUser;

    public function findByEmail(string $email): ?TempUser;

    public function delete(string $id): void;

    public function cleanupExpired(): int;
}

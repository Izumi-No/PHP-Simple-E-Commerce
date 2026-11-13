<?php

namespace Izumi\Backend\app\Repositories\Users;

use Izumi\Backend\app\Models\Auth\User;

interface IUsersRepository
{
    public function save(User $data): User;

    public function findById(string $id): ?User;

    /** @return array<User> */
    public function findAll(): array;

    public function update(string $id, array $data): void;

    public function delete(string $id): void;
}

<?php

namespace Izumi\Backend\app\Repositories\Users;

use Izumi\Backend\app\Models\Auth\User;

final class InMemoryUsersRepository implements IUsersRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function save(User $data): User
    {
        $this->users[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    /** @return array<User> */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function update(string $id, array $data): void
    {
        if (!isset($this->users[$id])) {
            throw new \Exception('User not found');
        }
        $user = $this->users[$id];
        $errors = $user->update($data);
        if ($errors !== []) {
            throw new \Exception('Invalid update data');
        }
        $this->users[$id] = $user;
    }

    public function delete(string $id): void
    {
        unset($this->users[$id]);
    }
}

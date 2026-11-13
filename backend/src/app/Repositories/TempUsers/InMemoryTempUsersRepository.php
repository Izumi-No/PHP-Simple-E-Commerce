<?php

namespace Izumi\Backend\app\Repositories\TempUsers;

use Izumi\Backend\app\Models\Auth\TempUser;

final class InMemoryTempUsersRepository implements ITempUsersRepository
{
    /** @var array<string, TempUser> */
    private array $tempUsers = [];

    public function save(TempUser $data): TempUser
    {
        $this->tempUsers[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?TempUser
    {
        return $this->tempUsers[$id] ?? null;
    }

    /** @return array<TempUser> */
    public function findAll(): array
    {
        return array_values($this->tempUsers);
    }

    public function findByEmail(string $email): ?TempUser
    {
        foreach ($this->tempUsers as $tempUser) {
            if ($tempUser->email === $email) {
                return $tempUser;
            }
        }
        return null;
    }

    public function delete(string $id): void
    {
        unset($this->tempUsers[$id]);
    }

    public function cleanupExpired(): int
    {
        $count = 0;
        foreach ($this->tempUsers as $id => $tempUser) {
            if ($tempUser->isExpired()) {
                unset($this->tempUsers[$id]);
                $count++;
            }
        }
        return $count;
    }
}

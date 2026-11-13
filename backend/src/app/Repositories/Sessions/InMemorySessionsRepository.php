<?php

namespace Izumi\Backend\app\Repositories\Sessions;

use Izumi\Backend\app\Models\Auth\Session;

final class InMemorySessionsRepository implements ISessionsRepository
{
    /** @var array<string, Session> */
    private array $sessions = [];

    public function save(Session $data): Session
    {
        $this->sessions[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?Session
    {
        return $this->sessions[$id] ?? null;
    }

    public function delete(string $id): void
    {
        unset($this->sessions[$id]);
    }

    public function cleanupExpired(): int
    {
        $count = 0;
        foreach ($this->sessions as $id => $session) {
            if ($session->isExpired()) {
                unset($this->sessions[$id]);
                $count++;
            }
        }
        return $count;
    }
}

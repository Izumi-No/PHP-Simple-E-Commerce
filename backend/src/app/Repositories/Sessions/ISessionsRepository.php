<?php

namespace Izumi\Backend\app\Repositories\Sessions;

use Izumi\Backend\app\Models\Auth\Session;

interface ISessionsRepository
{
    public function save(Session $data): Session;

    public function findById(string $id): ?Session;

    public function delete(string $id): void;

    public function cleanupExpired(): int;
}

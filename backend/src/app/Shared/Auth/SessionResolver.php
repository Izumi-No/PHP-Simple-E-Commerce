<?php

namespace Izumi\Backend\app\Shared\Auth;

use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Repositories\Sessions\ISessionsRepository;
use Izumi\Backend\app\Repositories\Users\IUsersRepository;

final class SessionResolver
{
    public const COOKIE_NAME = 'session_id';

    public function __construct(
        private readonly ISessionsRepository $sessions,
        private readonly IUsersRepository $users,
    ) {}

    /**
     * Resolves the current authenticated user from the session cookie.
     * Returns null if the cookie is missing, the session is expired,
     * deleted, or the user no longer exists.
     */
    public function resolve(?string $cookieValue): ?User
    {
        if ($cookieValue === null || $cookieValue === '') {
            return null;
        }

        $session = $this->sessions->findById($cookieValue);

        if ($session === null) {
            return null;
        }

        if ($session->isExpired()) {
            return null;
        }

        return $this->users->findById($session->user);
    }
}

<?php

namespace Izumi\Backend\app\Models\Auth\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class MissingPasswordHashError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'MissingPasswordHash', message: 'Password hash is required for password identities');
    }
}

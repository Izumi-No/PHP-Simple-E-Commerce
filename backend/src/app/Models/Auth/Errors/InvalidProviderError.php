<?php

namespace Izumi\Backend\app\Models\Auth\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidProviderError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidProvider', message: 'Provider must be one of: password, openid_connect');
    }
}

<?php

namespace Izumi\Backend\app\Models\Auth\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidEmailError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidEmail', message: 'Email must be a valid address');
    }
}

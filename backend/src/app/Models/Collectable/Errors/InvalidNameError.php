<?php

namespace Izumi\Backend\app\Models\Collectable\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidNameError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidName', message: 'Collectable name cannot be empty');
    }
}

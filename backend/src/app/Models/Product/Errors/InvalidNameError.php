<?php

namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidNameError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'InvalidName',
            message: 'Name cannot be empty',
        );
    }
}
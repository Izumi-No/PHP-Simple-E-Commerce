<?php

namespace Izumi\Backend\app\Models\Collectable\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidPriceError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'InvalidPrice',
            message: 'Price must be a non-negative decimal with at most 2 decimal places',
        );
    }
}

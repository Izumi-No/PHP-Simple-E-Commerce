<?php

namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidPriceError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'InvalidPrice',
            message: 'Price cannot be negative',
        );
    }
}
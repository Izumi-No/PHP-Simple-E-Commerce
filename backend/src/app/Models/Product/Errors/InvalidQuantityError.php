<?php

namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidQuantityError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'InvalidQuantity',
            message: 'Quantity cannot be negative',
        );
    }
}
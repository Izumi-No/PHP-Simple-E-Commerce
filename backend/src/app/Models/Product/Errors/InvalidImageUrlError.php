<?php

namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidImageUrlError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'InvalidImageUrl',
            message: 'Image URL is not valid',
        );
    }
}
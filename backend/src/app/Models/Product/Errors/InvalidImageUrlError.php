<?php

namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Error;

final class InvalidImageUrlError extends Error
{
    public function __construct()
    {
        parent::__construct(
            'InvalidImageUrl',
            'Image URL is not valid',
            4
        );
    }
}

?>

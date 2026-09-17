<?php

namespace Izumi\Backend\app\Models\Product\Errors;
use Izumi\Backend\app\Shared\Error;

final class InvalidNameError extends Error
{
    public function __construct()
    {
        parent::__construct(
            'InvalidName',
            'Name cannot be empty',
            1
        );
    }
}


?>

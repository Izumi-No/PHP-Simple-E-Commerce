<?php

namespace Izumi\Backend\app\Models\CollectableTag\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidCollectableError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidCollectable', message: 'Collectable reference cannot be empty');
    }
}

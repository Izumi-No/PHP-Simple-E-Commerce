<?php

namespace Izumi\Backend\app\Models\CollectableTag\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidTagError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidTag', message: 'Tag reference cannot be empty');
    }
}

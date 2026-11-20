<?php

namespace Izumi\Backend\app\Models\Collectable\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidDescriptionError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidDescription', message: 'Collectable description cannot be empty');
    }
}

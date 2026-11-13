<?php

namespace Izumi\Backend\app\Models\Auth\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidNicknameError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidNickname', message: 'Nickname cannot be empty');
    }
}

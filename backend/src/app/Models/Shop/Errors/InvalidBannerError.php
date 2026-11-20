<?php

namespace Izumi\Backend\app\Models\Shop\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class InvalidBannerError extends Error
{
    public function __construct()
    {
        parent::__construct(code: 'InvalidBanner', message: 'Banner must be a valid URL');
    }
}

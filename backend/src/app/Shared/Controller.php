<?php

namespace Izumi\Backend\app\Shared;

use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Errors\ErrorResponse;

abstract class Controller
{
    protected function response(mixed $data, int $status = 200): Response
    {
        return new Response($data, $status);
    }

    protected function error(Error $error, int $status): Response
    {
        return ErrorResponse::from(code: $error->code, message: $error->message, status: $status);
    }
}

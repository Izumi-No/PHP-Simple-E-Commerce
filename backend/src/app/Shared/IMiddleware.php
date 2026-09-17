<?php

namespace Izumi\Backend\app\Shared;

interface IMiddleware
{
    public function handle(
        callable $next
    ): mixed;
}

?>

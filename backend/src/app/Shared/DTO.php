<?php

namespace Izumi\Backend\app\Shared;

use JsonSerializable;

abstract readonly class DTO implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
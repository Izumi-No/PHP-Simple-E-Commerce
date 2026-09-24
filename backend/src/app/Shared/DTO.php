<?php

namespace Izumi\Backend\app\Shared;

use JsonSerializable;

abstract readonly class DTO implements JsonSerializable
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
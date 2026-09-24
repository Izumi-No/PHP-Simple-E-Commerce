<?php

namespace Izumi\Backend\app\Shared\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Min
{
    public function __construct(
        public int|float $value,
    ) {}
}

<?php

namespace Izumi\Backend\app\Shared\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ApiProperty
{
    public function __construct(
        public ?string $description = null,
        public mixed $example = null,
        public ?string $format = null,
        public bool $deprecated = false,
    ) {}
}

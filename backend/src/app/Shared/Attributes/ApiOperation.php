<?php

namespace Izumi\Backend\app\Shared\Attributes;

use Attribute;
use Izumi\Backend\app\Shared\DTO;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class ApiOperation
{
    public function __construct(
        public string $summary,
        /** @var class-string<DTO>|null */
        public ?string $request = null,
        public array $tags = [],
    ) {}
}
<?php

namespace Izumi\Backend\app\Shared\Attributes;

use Attribute;
use Izumi\Backend\app\Shared\DTO;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class ApiResponse
{
    public function __construct(
        public int $status,
        public string $description,
        /** @var class-string<DTO>|null */
        public ?string $dto = null,
    ) {}
}
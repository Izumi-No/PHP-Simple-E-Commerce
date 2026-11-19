<?php

namespace Izumi\Backend\app\DTOs\Tag;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class TagCreateDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Tag name', example: 'Retro Cards')]
        #[MinLength(1)]
        #[MaxLength(255)]
        public string $name,
    ) {}
}

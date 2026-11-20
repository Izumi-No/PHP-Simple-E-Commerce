<?php

namespace Izumi\Backend\app\DTOs\Tag;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class TagResponseDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Tag ID')]
        public string $id,

        #[ApiProperty(description: 'Tag name', example: 'Retro Cards')]
        public string $name,

        #[ApiProperty(description: 'Shop ID')]
        public string $shop,
    ) {}
}

<?php

declare(strict_types=1);

namespace Izumi\Backend\app\DTOs\Collectable;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class CollectableRequestDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Collectable name', example: 'Rare Card')]
        #[MinLength(1)]
        #[MaxLength(255)]
        public string $name,

        #[ApiProperty(description: 'Collectable description', example: 'A rare trading card from the base set.')]
        #[MinLength(1)]
        public string $description,

        #[ApiProperty(description: 'Price as a decimal string', example: '49.90')]
        #[MinLength(1)]
        public string $price,

        #[ApiProperty(description: 'List of image URLs', example: ['https://example.com/card-front.jpg'])]
        public array $images = [],
    ) {}
}

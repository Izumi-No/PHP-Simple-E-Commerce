<?php

declare(strict_types=1);

namespace Izumi\Backend\app\DTOs\Collectable;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class CollectableResponseDTO extends DTO
{
    /** @param list<string> $images */
    public function __construct(
        #[ApiProperty(description: 'Collectable ID')]
        public string $id,

        #[ApiProperty(description: 'Collectable name')]
        public string $name,

        #[ApiProperty(description: 'Collectable description')]
        public string $description,

        #[ApiProperty(description: 'List of image URLs')]
        public array $images,

        #[ApiProperty(description: 'Price as a decimal string', example: '49.90')]
        public string $price,

        #[ApiProperty(description: 'Shop ID')]
        public string $shop,

        #[ApiProperty(description: 'Tag names associated with this collectable')]
        public array $tags,
    ) {}
}

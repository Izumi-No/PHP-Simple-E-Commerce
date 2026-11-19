<?php

namespace Izumi\Backend\app\DTOs\Shop;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class ShopResponseDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Shop ID')]
        public string $id,

        #[ApiProperty(description: 'Shop name', example: 'Cool Collectibles')]
        public string $name,

        #[ApiProperty(description: 'Shop banner URL')]
        public ?string $banner,

        #[ApiProperty(description: 'Owner user ID')]
        public string $owner,
    ) {}
}

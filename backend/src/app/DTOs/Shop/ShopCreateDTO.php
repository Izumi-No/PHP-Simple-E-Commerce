<?php

namespace Izumi\Backend\app\DTOs\Shop;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class ShopCreateDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Shop name', example: 'Cool Collectibles')]
        #[MinLength(1)]
        #[MaxLength(255)]
        public string $name,

        #[ApiProperty(description: 'Shop banner URL', example: 'https://example.com/banner.jpg')]
        public ?string $banner = null,
    ) {}
}

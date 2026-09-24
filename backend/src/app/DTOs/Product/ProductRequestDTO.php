<?php

namespace Izumi\Backend\app\DTOs\Product;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\Min;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class ProductRequestDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Product name', example: 'Wireless Mouse')]
        #[MinLength(1)]
        #[MaxLength(120)]
        public string $name,

        #[ApiProperty(description: 'Product price', example: 49.9)]
        #[Min(0)]
        public float $price,

        #[ApiProperty(description: 'Product description', example: 'Ergonomic wireless mouse')]
        public string $description,

        #[ApiProperty(description: 'Quantity in stock', example: 15)]
        #[Min(0)]
        public int $quantity,

        #[ApiProperty(description: 'Product image URL', example: 'https://example.com/image.jpg')]
        public string $image_url,
    ) {}
}
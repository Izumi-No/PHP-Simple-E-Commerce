<?php

namespace Izumi\Backend\app\DTOs\Product;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\Min;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class ProductResponseDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'Product id (UUID v4)', example: '1f9e2c3a-9b21-4c5d-8e6f-7a8b9c0d1e2f')]
        public string $id,

        #[ApiProperty(description: 'Product name', example: 'Wireless Mouse')]
        #[MinLength(1)]
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
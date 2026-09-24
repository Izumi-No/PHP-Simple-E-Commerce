<?php

namespace Izumi\Backend\app\DTOs\Product;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class ProductItemResponseDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'The product')]
        public ProductResponseDTO $data,
    ) {}
}
<?php

namespace Izumi\Backend\app\DTOs\Product;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class ProductCollectionResponseDTO extends DTO
{
    /**
     * @param list<ProductResponseDTO> $data
     */
    public function __construct(
        #[ApiProperty(description: 'List of products')]
        public array $data,
    ) {}
}
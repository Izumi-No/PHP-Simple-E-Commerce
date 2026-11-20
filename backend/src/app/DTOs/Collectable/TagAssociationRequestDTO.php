<?php

declare(strict_types=1);

namespace Izumi\Backend\app\DTOs\Collectable;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class TagAssociationRequestDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'List of tag IDs to associate', example: ['tag-id-1', 'tag-id-2'])]
        public array $tags,
    ) {}
}

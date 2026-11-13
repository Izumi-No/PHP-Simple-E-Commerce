<?php

namespace Izumi\Backend\app\DTOs\Auth;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;

final readonly class AuthMeResponseDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'User ID')]
        public string $id,

        #[ApiProperty(description: 'User name', example: 'Kevyn Ribeiro')]
        public string $name,

        #[ApiProperty(description: 'User nickname', example: 'izumi')]
        public string $nickname,
    ) {}
}

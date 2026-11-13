<?php

namespace Izumi\Backend\app\DTOs\Auth;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\Attributes\Email;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;

final readonly class AuthRegisterDTO extends DTO
{
    public function __construct(
        #[ApiProperty(description: 'User email', example: 'user@example.com')]
        #[Email]
        public string $email,

        #[ApiProperty(description: 'Password (min 8 characters)', example: 's3cret!')]
        #[MinLength(8)]
        public string $password,
    ) {}
}

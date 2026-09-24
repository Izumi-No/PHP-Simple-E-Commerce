<?php

namespace Izumi\Backend\app\Shared\Errors;

abstract readonly class Error
{
    public function __construct(
        public string $code,
        public string $message,
    ) {}

    /** @return array{code: string, message: string} */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}

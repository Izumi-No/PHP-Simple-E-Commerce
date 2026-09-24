<?php

namespace Izumi\Backend\app\Shared\Errors;

final readonly class ValidationError
{
    public function __construct(
        public string $field,
        public string $code,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}

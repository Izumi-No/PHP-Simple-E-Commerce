<?php

namespace Izumi\Backend\app\Shared;

final class Response
{
    public function __construct(
        public readonly mixed $body,
        public readonly int $status = 200,
        public readonly array $headers = [
            'Content-Type' => 'application/json',
        ],
    ) {}

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($this->body, JSON_THROW_ON_ERROR);
    }
}

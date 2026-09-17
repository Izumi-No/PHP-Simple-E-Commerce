<?php

namespace Izumi\Backend\app\Shared;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly string $body = '',
        public readonly array $query = [],
        public readonly array $params = [],
    ) {}

    public function json(): array
    {
        $data = json_decode(
            $this->body,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($data)) {
            throw new \JsonException(
                'Request body must contain a JSON object.'
            );
        }

        return $data;
    }
}

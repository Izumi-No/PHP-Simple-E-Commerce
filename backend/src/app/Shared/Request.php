<?php

namespace Izumi\Backend\app\Shared;

use Izumi\Backend\app\Shared\Validation\DTOValidator;
use Izumi\Backend\app\Shared\Validation\ValidationResult;
use JsonException;

/**
 * @template T of DTO
 */
final readonly class Request
{
    public function __construct(
        public string $method,
        public string $uri,
        public string $body = '',
        public array $query = [],
        public array $params = [],
    ) {}

    /** @return array<string, mixed> */
    public function json(): array
    {
        $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data) || array_is_list($data)) {
            throw new JsonException('Request body must contain a JSON object.');
        }

        return $data;
    }

    /**
     * @template U of DTO
     * @param class-string<U> $class
     * @return ValidationResult<U>
     */
    public function validate(string $class, ?DTOValidator $validator = null): ValidationResult
    {
        return ($validator ?? new DTOValidator())->validate($class, $this->json());
    }
}

<?php

namespace Izumi\Backend\app\Shared\Validation;

use Izumi\Backend\app\Shared\DTO;
use Izumi\Backend\app\Shared\Errors\ValidationError;
use LogicException;

/**
 * @template T of DTO
 */
final readonly class ValidationResult
{
    /**
     * @param T|null $data
     * @param list<ValidationError> $errors
     */
    public function __construct(
        public ?DTO $data,
        public array $errors = [],
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function isInvalid(): bool
    {
        return !$this->isValid();
    }

    /** @return T */
    public function value(): DTO
    {
        if ($this->data === null) {
            throw new LogicException('Cannot retrieve DTO from an invalid result.');
        }

        return $this->data;
    }
}

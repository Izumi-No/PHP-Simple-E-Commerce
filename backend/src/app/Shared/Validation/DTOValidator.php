<?php

namespace Izumi\Backend\app\Shared\Validation;

use Izumi\Backend\app\Shared\Attributes\Email;
use Izumi\Backend\app\Shared\Attributes\Max;
use Izumi\Backend\app\Shared\Attributes\MaxLength;
use Izumi\Backend\app\Shared\Attributes\Min;
use Izumi\Backend\app\Shared\Attributes\MinLength;
use Izumi\Backend\app\Shared\DTO;
use Izumi\Backend\app\Shared\Errors\ValidationError;
use ReflectionClass;
use ReflectionNamedType;

final class DTOValidator
{
    public function __construct(
        private readonly DTOHydrator $hydrator = new DTOHydrator(),
    ) {}

    /**
     * @template T of DTO
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return ValidationResult<T>
     */
    public function validate(string $class, array $data): ValidationResult
    {
        $reflection = new ReflectionClass($class);
        $errors = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $property = $reflection->getProperty($name);

            if (!array_key_exists($name, $data)) {
                if (!$parameter->isDefaultValueAvailable()) {
                    $errors[] = new ValidationError(field: $name, code: 'REQUIRED', message: 'This field is required.');
                }

                continue;
            }

            $value = $data[$name];
            $type = $parameter->getType();

            if ($value === null) {
                if ($type?->allowsNull()) {
                    continue;
                }

                $errors[] = new ValidationError(
                    field: $name,
                    code: 'INVALID_TYPE',
                    message: 'This field cannot be null.',
                );

                continue;
            }

            if ($type instanceof ReflectionNamedType && !$this->matchesType($type->getName(), $value)) {
                $errors[] = new ValidationError(
                    field: $name,
                    code: 'INVALID_TYPE',
                    message: 'The field has an invalid type.',
                );

                continue;
            }

            foreach ($property->getAttributes() as $attribute) {
                $rule = $attribute->newInstance();

                $message = match (true) {
                    $rule instanceof Min && is_numeric($value) && $value < $rule->value
                        => "Must be greater than or equal to {$rule->value}.",
                    $rule instanceof Max && is_numeric($value) && $value > $rule->value
                        => "Must be less than or equal to {$rule->value}.",
                    $rule instanceof MinLength && is_string($value) && mb_strlen($value) < $rule->value
                        => "Must contain at least {$rule->value} characters.",
                    $rule instanceof MaxLength && is_string($value) && mb_strlen($value) > $rule->value
                        => "Must contain at most {$rule->value} characters.",
                    $rule instanceof Email && is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false
                        => 'Must be a valid email address.',
                    default => null,
                };

                if ($message !== null) {
                    $errors[] = new ValidationError(
                        field: $name,
                        code: strtoupper(new \ReflectionClass($rule)->getShortName()),
                        message: $message,
                    );
                }
            }
        }

        if ($errors !== []) {
            return new ValidationResult(data: null, errors: $errors);
        }

        return new ValidationResult(data: $this->hydrator->hydrate($class, $data));
    }

    private function matchesType(string $type, mixed $value): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value) || is_int($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'mixed' => true,
            default => $value instanceof $type,
        };
    }
}

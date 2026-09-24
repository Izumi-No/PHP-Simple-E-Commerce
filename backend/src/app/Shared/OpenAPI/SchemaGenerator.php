<?php

namespace Izumi\Backend\app\Shared\OpenAPI;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;
use ReflectionClass;
use ReflectionNamedType;

final class SchemaGenerator
{
    /**
     * @param class-string<DTO> $class
     */
    public function generate(string $class): array
    {
        $reflection = new ReflectionClass($class);

        $properties = [];
        $required = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            $property = $reflection->getProperty($name);

            $schema = [
                'type' => $this->openApiType($type instanceof ReflectionNamedType ? $type->getName() : 'string'),
            ];

            if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
                $schema['type'] = [
                    $schema['type'],
                    'null',
                ];
            }

            foreach ($property->getAttributes(ApiProperty::class) as $attribute) {
                $metadata = $attribute->newInstance();

                if ($metadata->description !== null) {
                    $schema['description'] = $metadata->description;
                }

                if ($metadata->example !== null) {
                    $schema['example'] = $metadata->example;
                }

                if ($metadata->format !== null) {
                    $schema['format'] = $metadata->format;
                }

                if ($metadata->deprecated) {
                    $schema['deprecated'] = true;
                }
            }

            $properties[$name] = $schema;

            if (!$parameter->isOptional()) {
                $required[] = $name;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    private function openApiType(string $type): string
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'array' => 'array',
            default => 'string',
        };
    }
}

<?php

namespace Izumi\Backend\app\Shared\OpenAPI;

use Izumi\Backend\app\Shared\Attributes\ApiProperty;
use Izumi\Backend\app\Shared\DTO;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;

final class SchemaGenerator
{
    /** @var array<class-string<DTO>, array<string, mixed>> */
    private array $schemas = [];

    /**
     * @param class-string<DTO> $class
     * @return array<string, mixed>
     */
    public function generate(string $class): array
    {
        if (isset($this->schemas[$class])) {
            return $this->schemas[$class];
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $doc = $constructor?->getDocComment() ?: '';

        $properties = [];
        $required = [];

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            $schema = $this->propertySchema($class, $name, $type, $doc);

            foreach ($reflection->getProperty($name)->getAttributes(ApiProperty::class) as $attribute) {
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

        $this->schemas[$class] = $schema;

        return $schema;
    }

    /**
     * @return array<class-string<DTO>, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->schemas;
    }

    /**
     * @param class-string<DTO> $class
     */
    private function propertySchema(string $class, string $name, ?ReflectionType $type, string $doc): array
    {
        if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
            $schema = ['type' => 'array'];

            $item = $this->docListType($doc, $name, $class);

            if ($item !== null) {
                if (class_exists($item) && is_a($item, DTO::class, true)) {
                    /** @var class-string<DTO> $item */
                    $this->generate($item);

                    $schema['items'] = [
                        '$ref' => '#/components/schemas/' . new ReflectionClass($item)->getShortName(),
                    ];
                } else {
                    $schema['items'] = ['type' => $this->openApiType($item)];
                }
            }

            return $schema;
        }

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $target = $type->getName();

            if (class_exists($target) && is_a($target, DTO::class, true)) {
                /** @var class-string<DTO> $target */
                $this->generate($target);

                return [
                    '$ref' => '#/components/schemas/' . new ReflectionClass($target)->getShortName(),
                ];
            }
        }

        if ($type instanceof ReflectionNamedType) {
            $schema = [
                'type' => $this->openApiType($type->getName()),
            ];

            if ($type->allowsNull()) {
                $schema['type'] = [
                    $schema['type'],
                    'null',
                ];
            }

            return $schema;
        }

        return ['type' => 'string'];
    }

    /**
     * Resolves a documented list element type (e.g. `list<Foo>`, `array<Foo>`, `Foo[]`).
     *
     * @param class-string<DTO> $class
     */
    private function docListType(string $doc, string $name, string $class): ?string
    {
        if (!preg_match('/@param\s+([^\s]+)\s+\$' . preg_quote($name, '/') . '\b/', $doc, $match)) {
            return null;
        }

        $raw = $match[1];

        if (!preg_match('/^(?:list|array)<([^,>]+)>$/', $raw, $item) && !preg_match('/^([^\s]+)\[\]$/', $raw, $item)) {
            return null;
        }

        $element = trim($item[1]);

        if ($element === '' || $element === 'mixed') {
            return null;
        }

        if (class_exists($element)) {
            return $element;
        }

        $candidate = new ReflectionClass($class)->getNamespaceName() . '\\' . $element;

        return class_exists($candidate) ? $candidate : null;
    }

    private function openApiType(string $type): string
    {
        return match ($type) {
            'int', 'integer' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'array' => 'array',
            default => 'string',
        };
    }
}

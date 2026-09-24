<?php

namespace Izumi\Backend\app\Shared\Validation;

use Izumi\Backend\app\Shared\DTO;
use ReflectionClass;

/**
 * @template T of DTO
 */
final class DTOHydrator
{
    /**
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return T
     */
    public function hydrate(string $class, array $data): DTO
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $data)) {
                $arguments[] = $data[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            // A validação deve impedir que cheguemos aqui.
            throw new \LogicException("Missing validated DTO field: {$name}");
        }

        return $reflection->newInstanceArgs($arguments);
    }
}

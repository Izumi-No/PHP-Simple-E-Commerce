<?php

namespace Izumi\Backend\app\Shared;


abstract class Error {

    public function __construct(public string $name, public string $message, public int $code) {}

    public function toArray(): array {
        return [
            'name' => $this->name,
            'message' => $this->message,
            'code' => $this->code
        ];
    }

}

/**
 * @param array<\Izumi\Backend\app\Shared\Error> $errors
 *
 * @return array<mixed>
 */
function map_errors_to_arrays(array $errors): array {
    return array_map(fn($error) => $error->toArray(), $errors);
}

?>

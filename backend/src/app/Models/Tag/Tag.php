<?php

namespace Izumi\Backend\app\Models\Tag;

use Izumi\Backend\app\Models\Tag\Errors\InvalidNameError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class Tag extends Model
{
    private function __construct(
        public string $name,
        public string $shop,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{name?: string, shop: string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $name = $data['name'] ?? '';
        $shop = $data['shop'] ?? '';

        $errors = self::validate($name);

        if ($errors !== []) {
            return $errors;
        }

        return new self($name, $shop);
    }

    /**
     * @param array{id: string, name: string, shop: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['shop'], $data['id']);
    }

    /**
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $name = $data['name'] ?? $this->name;

        $errors = self::validate($name);

        if ($errors !== []) {
            return $errors;
        }

        $this->name = $name;

        return [];
    }

    /**
     * @return array{id: string, name: string, shop: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shop' => $this->shop,
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(mixed $name): array
    {
        $errors = [];

        if (!is_string($name) || trim($name) === '') {
            $errors[] = new InvalidNameError();
        }

        return $errors;
    }
}

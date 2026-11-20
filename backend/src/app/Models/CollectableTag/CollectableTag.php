<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Models\CollectableTag;

use Izumi\Backend\app\Models\CollectableTag\Errors\InvalidCollectableError;
use Izumi\Backend\app\Models\CollectableTag\Errors\InvalidTagError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class CollectableTag extends Model
{
    private function __construct(
        public string $collectable,
        public string $tag,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{collectable?: string, tag?: string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $collectable = $data['collectable'] ?? '';
        $tag = $data['tag'] ?? '';

        $errors = self::validate($collectable, $tag);
        if ($errors !== []) {
            return $errors;
        }

        return new self($collectable, $tag);
    }

    /**
     * @param array{id: string, collectable: string, tag: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['collectable'], $data['tag'], $data['id']);
    }

    /**
     * @param array{collectable?: string, tag?: string} $data
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $collectable = $data['collectable'] ?? $this->collectable;
        $tag = $data['tag'] ?? $this->tag;

        $errors = self::validate($collectable, $tag);
        if ($errors !== []) {
            return $errors;
        }

        $this->collectable = $collectable;
        $this->tag = $tag;

        return [];
    }

    /**
     * @return array{id: string, collectable: string, tag: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'collectable' => $this->collectable,
            'tag' => $this->tag,
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(string $collectable, string $tag): array
    {
        $errors = [];

        if (trim($collectable) === '') {
            $errors[] = new InvalidCollectableError();
        }

        if (trim($tag) === '') {
            $errors[] = new InvalidTagError();
        }

        return $errors;
    }
}

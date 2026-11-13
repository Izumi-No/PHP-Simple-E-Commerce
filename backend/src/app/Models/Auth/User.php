<?php

namespace Izumi\Backend\app\Models\Auth;

use Izumi\Backend\app\Models\Auth\Errors\InvalidNameError;
use Izumi\Backend\app\Models\Auth\Errors\InvalidNicknameError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class User extends Model
{
    private function __construct(
        public string $name,
        public string $nickname,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{name?: string, nickname?: string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $name = $data['name'] ?? '';
        $nickname = $data['nickname'] ?? '';

        $errors = self::validate($name, $nickname);

        if ($errors !== []) {
            return $errors;
        }

        return new self($name, $nickname);
    }

    /**
     * @param array{id: string, name: string, nickname: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['nickname'], $data['id']);
    }

    /**
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $name = $data['name'] ?? $this->name;
        $nickname = $data['nickname'] ?? $this->nickname;

        $errors = self::validate($name, $nickname);

        if ($errors !== []) {
            return $errors;
        }

        $this->name = $name;
        $this->nickname = $nickname;

        return [];
    }

    /**
     * @return array{ id: string, name: string, nickname: string }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(mixed $name, mixed $nickname): array
    {
        $errors = [];

        if (!is_string($name) || trim($name) === '') {
            $errors[] = new InvalidNameError();
        }

        if (!is_string($nickname) || trim($nickname) === '') {
            $errors[] = new InvalidNicknameError();
        }

        return $errors;
    }
}

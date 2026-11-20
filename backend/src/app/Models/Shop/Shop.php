<?php

namespace Izumi\Backend\app\Models\Shop;

use Izumi\Backend\app\Models\Shop\Errors\InvalidBannerError;
use Izumi\Backend\app\Models\Shop\Errors\InvalidNameError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class Shop extends Model
{
    private function __construct(
        public string $name,
        public ?string $banner,
        public string $owner,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{name?: string, banner?: ?string, owner: string} $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $name = $data['name'] ?? '';
        $banner = $data['banner'] ?? null;
        $owner = $data['owner'] ?? '';

        $errors = self::validate($name, $banner);

        if ($errors !== []) {
            return $errors;
        }

        return new self($name, $banner, $owner);
    }

    /**
     * @param array{id: string, name: string, banner: ?string, owner: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['banner'] ?? null, $data['owner'], $data['id']);
    }

    /**
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $name = $data['name'] ?? $this->name;
        $banner = array_key_exists('banner', $data) ? $data['banner'] : $this->banner;

        $errors = self::validate($name, $banner);

        if ($errors !== []) {
            return $errors;
        }

        $this->name = $name;
        $this->banner = $banner;

        return [];
    }

    /**
     * @return array{id: string, name: string, banner: ?string, owner: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'banner' => $this->banner,
            'owner' => $this->owner,
        ];
    }

    /**
     * @return list<Error>
     */
    private static function validate(mixed $name, mixed $banner): array
    {
        $errors = [];

        if (!is_string($name) || trim($name) === '') {
            $errors[] = new InvalidNameError();
        }

        if ($banner !== null && !is_string($banner)) {
            $errors[] = new InvalidBannerError();
        }

        if (is_string($banner) && $banner !== '' && filter_var($banner, FILTER_VALIDATE_URL) === false) {
            $errors[] = new InvalidBannerError();
        }

        return $errors;
    }
}

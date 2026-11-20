<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Models\Collectable;

use Izumi\Backend\app\Models\Collectable\Errors\InvalidDescriptionError;
use Izumi\Backend\app\Models\Collectable\Errors\InvalidImageUrlError;
use Izumi\Backend\app\Models\Collectable\Errors\InvalidNameError;
use Izumi\Backend\app\Models\Collectable\Errors\InvalidPriceError;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Model;

final class Collectable extends Model
{
    /** @param list<string> $images */
    private function __construct(
        public string $name,
        public string $description,
        public array $images,
        public string $price,
        public string $shop,
        string $id = '',
    ) {
        parent::__construct($id);
    }

    /**
     * @param array{
     *     name?: string,
     *     description?: string,
     *     images?: list<string>,
     *     price?: string|float|int,
     *     shop: string
     * } $data
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $images = $data['images'] ?? [];
        $price = $data['price'] ?? '';
        $shop = $data['shop'] ?? '';

        $errors = self::validate($name, $description, $images, $price);
        if ($errors !== []) {
            return $errors;
        }

        return new self($name, $description, $images, self::normalizePrice($price), $shop);
    }

    /**
     * @param array{id: string, name: string, description: string, images: list<string>, price: string, shop: string} $data
     */
    public static function fromArray(array $data): self
    {
        // images may arrive as a JSON string from JSONB columns
        $images = $data['images'];
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        return new self(
            $data['name'],
            $data['description'],
            $images,
            self::normalizePrice($data['price']),
            $data['shop'],
            $data['id'],
        );
    }

    /**
     * @param array{name?: string, description?: string, images?: list<string>, price?: string|float|int, shop?: string} $data
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $name = $data['name'] ?? $this->name;
        $description = $data['description'] ?? $this->description;
        $images = $data['images'] ?? $this->images;
        $price = $data['price'] ?? $this->price;

        $errors = self::validate($name, $description, $images, $price);
        if ($errors !== []) {
            return $errors;
        }

        $this->name = $name;
        $this->description = $description;
        $this->images = $images;
        $this->price = self::normalizePrice($price);

        return [];
    }

    /**
     * @return array{id: string, name: string, description: string, images: list<string>, price: string, shop: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'images' => $this->images,
            'price' => $this->price,
            'shop' => $this->shop,
        ];
    }

    /**
     * Normalize a price value (string, int or float) to a 2-decimal string using BCMath.
     * Never uses PHP float arithmetic — only string comparison.
     *
     * @param string|float|int $price
     */
    private static function normalizePrice(string|float|int $price): string
    {
        $priceStr = is_string($price) ? $price : (string) $price;
        return bcadd($priceStr, '0', 2);
    }

    /**
     * @param list<string> $images
     * @return list<Error>
     */
    private static function validate(string $name, string $description, array $images, string|float|int $price): array
    {
        $errors = [];

        if (trim($name) === '') {
            $errors[] = new InvalidNameError();
        }

        if (trim($description) === '') {
            $errors[] = new InvalidDescriptionError();
        }

        if (!self::isValidPrice($price)) {
            $errors[] = new InvalidPriceError();
        }

        foreach ($images as $image) {
            if (!is_string($image) || $image === '' || filter_var($image, FILTER_VALIDATE_URL) === false) {
                $errors[] = new InvalidImageUrlError();
                break;
            }
        }

        return $errors;
    }

    /**
     * Validate a price is numeric, non-negative, and has at most 2 decimal places.
     *
     * @param string|float|int $price
     */
    private static function isValidPrice(string|float|int $price): bool
    {
        $priceStr = is_string($price) ? $price : (string) $price;

        // Must be a valid number
        if (!is_numeric($priceStr)) {
            return false;
        }

        // Must be non-negative (bccomp returns -1 if a < b)
        if (bccomp($priceStr, '0', 2) < 0) {
            return false;
        }

        // Must have at most 2 decimal places
        $parts = explode('.', $priceStr);
        if (count($parts) === 2 && strlen($parts[1]) > 2) {
            return false;
        }

        return true;
    }
}

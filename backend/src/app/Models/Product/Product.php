<?php

namespace Izumi\Backend\app\Models\Product;

use Izumi\Backend\app\Shared\Error;
use Izumi\Backend\app\Shared\Model;
use Izumi\Backend\app\Models\Product\Errors\InvalidImageUrlError;
use Izumi\Backend\app\Models\Product\Errors\InvalidNameError;
use Izumi\Backend\app\Models\Product\Errors\InvalidPriceError;
use Izumi\Backend\app\Models\Product\Errors\InvalidQuantityError;

final class Product extends Model
{
    private function __construct(
        public string $name,
        public float $price,
        public string $description,
        public int $quantity,
        public string $image_url,
        string $id = ''
    ) {
        parent::__construct($id);
    }

    /**
     * Creates a new product from request data.
     *
     * @return list<Error>|self
     */
    public static function create(array $data): array|self
    {
        $name = $data['name'] ?? '';
        $price = $data['price'] ?? 0;
        $description = $data['description'] ?? '';
        $quantity = $data['quantity'] ?? 0;
        $imageUrl = $data['image_url'] ?? '';

        $errors = self::validate(
            $name,
            $price,
            $quantity,
            $imageUrl
        );

        if ($errors !== []) {
            return $errors;
        }

        return new self(
            $name,
            (float) $price,
            $description,
            $quantity,
            $imageUrl
        );
    }

    /**
     * Reconstructs a product from persistent data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            (float) $data['price'],
            $data['description'],
            (int) $data['quantity'],
            $data['image_url'],
            $data['id']
        );
    }

    /**
     * Updates the product with the supplied fields.
     *
     * @return list<Error>
     */
    public function update(array $data): array
    {
        $name = $data['name'] ?? $this->name;
        $price = $data['price'] ?? $this->price;
        $description = $data['description'] ?? $this->description;
        $quantity = $data['quantity'] ?? $this->quantity;
        $imageUrl = $data['image_url'] ?? $this->image_url;

        $errors = self::validate(
            $name,
            $price,
            $quantity,
            $imageUrl
        );

        if ($errors !== []) {
            return $errors;
        }

        $this->name = $name;
        $this->price = (float) $price;
        $this->description = $description;
        $this->quantity = (int) $quantity;
        $this->image_url = $imageUrl;

        return [];
    }

    /**
     * Converts the product to an array suitable for JSON responses
     * and persistence.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'image_url' => $this->image_url,
        ];
    }

    /**
     * Validates product fields.
     *
     * @return list<Error>
     */
    private static function validate(
        mixed $name,
        mixed $price,
        mixed $quantity,
        mixed $imageUrl
    ): array {
        $errors = [];

        if (!is_string($name) || trim($name) === '') {
            $errors[] = new InvalidNameError();
        }

        if (!is_numeric($price) || $price < 0) {
            $errors[] = new InvalidPriceError();
        }

        if (!is_int($quantity) || $quantity < 0) {
            $errors[] = new InvalidQuantityError();
        }

        if (
            !is_string($imageUrl) ||
            !filter_var($imageUrl, FILTER_VALIDATE_URL)
        ) {
            $errors[] = new InvalidImageUrlError();
        }

        return $errors;
    }

}

/**
 * @param array<Product> $products
 *
 * @return array<array<string, mixed>>
 */
function map_products_to_arrays(array $products): array
{
    return array_map(
        fn(Product $product) => $product->toArray(),
        $products
    );
}

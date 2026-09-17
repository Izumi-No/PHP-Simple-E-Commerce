<?php

namespace Izumi\Backend\app\Repositories\Products;

use Izumi\Backend\app\Models\Product\Product;



final class InMemoryProductsRepository implements IProductsRepository {
    private array $products = [];

    public function save(Product $data): Product {
        $this->products[$data->id] = $data;
        return $data;
    }

    public function findById(string $id): ?Product {
        return $this->products[$id] ?? null;
    }

    public function findAll(): array {
        return array_values($this->products);
    }

    public function update(string $id, array $data): void {
        if (!isset($this->products[$id])) {
            throw new \Exception("Product not found");
        }
        foreach ($data as $key => $value) {
            if (property_exists($this->products[$id], $key)) {
                $this->products[$id]->$key = $value;
            }
        }
    }

    public function delete(string $id): void {
        unset($this->products[$id]);
    }
}


?>

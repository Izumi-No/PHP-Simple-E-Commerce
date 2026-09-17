<?php

namespace Izumi\Backend\app\Repositories\Products;

use Izumi\Backend\app\Models\Product\Product;

interface IProductsRepository {
    public function save(Product $data): Product;
    public function findById(string $id): ?Product;
    /**
     * @return array<Product>
     */
    public function findAll(): array;
    public function update(string $id, array $data): void;
    public function delete(string $id): void;
}

?>

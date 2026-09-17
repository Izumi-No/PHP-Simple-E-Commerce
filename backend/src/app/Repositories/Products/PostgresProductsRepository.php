<?php
namespace Izumi\Backend\app\Repositories\Products;
use InvalidArgumentException;
use Izumi\Backend\app\Models\Product\Product;
use PDO;
use PDOException;
use RuntimeException;
final class PostgresProductsRepository implements IProductsRepository
{
    public function __construct(private PDO $pdo) {}
    public function save(Product $data): Product
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO products ( name, price, description, quantity, image_url ) VALUES ( :name, :price, :description, :quantity, :image_url ) RETURNING *",
            );
            $stmt->execute([
                ":name" => $data->name,
                ":price" => $data->price,
                ":description" => $data->description,
                ":quantity" => $data->quantity,
                ":image_url" => $data->image_url,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                throw new RuntimeException(
                    "Product was not returned after creation.",
                );
            }
            return Product::fromArray($row);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Failed to create product: " . $e->getMessage(),
                previous: $e,
            );
        }
    }
    public function findById(string $id): ?Product
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products WHERE id = :id AND deleted_at IS NULL",
        );
        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return Product::fromArray($row);
    }
    /** * @return array<Product> */ public function findAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM products WHERE deleted_at IS NULL ORDER BY created_at DESC",
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(
            fn(array $row): Product => Product::fromArray($row),
            $rows,
        );
    }
    public function update(string $id, array $data): void
    {
        $allowedFields = [
            "name",
            "price",
            "description",
            "quantity",
            "image_url",
        ];
        $setClause = [];
        $params = [":id" => $id];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields, true)) {
                continue;
            }
            $setClause[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        if ($setClause === []) {
            throw new InvalidArgumentException("No valid fields to update.");
        }
        $setClause[] = "updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare(
            "UPDATE products SET " .
                implode(", ", $setClause) .
                " WHERE id = :id AND deleted_at IS NULL",
        );
        $stmt->execute($params);
    }
    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL",
        );
        $stmt->execute([":id" => $id]);
    }
}

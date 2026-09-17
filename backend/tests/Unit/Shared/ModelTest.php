<?php
declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Model;
use PHPUnit\Framework\TestCase;

final class TestModel extends Model
{
    private function __construct(string $id = "")
    {
        parent::__construct($id);
    }

    /**
     * @param array{id?: string} $data
     */
    public static function create(array $data): self
    {
        return new self($data["id"] ?? "");
    }
}

final class AnotherTestModel extends Model
{
    private function __construct(string $id = "")
    {
        parent::__construct($id);
    }

    /**
     * @param array{id?: string} $data
     */
    public static function create(array $data): self
    {
        return new self($data["id"] ?? "");
    }
}

final class ModelTest extends TestCase
{
    public function testCreatesModelWithProvidedId(): void
    {
        $model = TestModel::create([
            "id" => "test-id",
        ]);

        self::assertSame("test-id", $model->id);
    }

    public function testGeneratesUuidWhenIdIsNotProvided(): void
    {
        $model = TestModel::create([]);

        self::assertNotEmpty($model->id);
        self::assertTrue(
            uuid_is_valid($model->id),
            "Model ID must be a valid UUID.",
        );
    }

    public function testGeneratesDifferentUuidForEachModel(): void
    {
        $model = TestModel::create([]);
        $otherModel = TestModel::create([]);

        self::assertNotSame($model->id, $otherModel->id);
    }

    public function testEqualsReturnsTrueForSameInstance(): void
    {
        $model = TestModel::create([
            "id" => "test-id",
        ]);

        if (!($model instanceof TestModel)) {
            $this->fail("Failed to create TestModel instance.");
        }

        self::assertTrue($model->equals($model));
    }
    public function testEqualsReturnsTrueForModelsWithSameClassAndId(): void
    {
        $model = TestModel::create([
            "id" => "test-id",
        ]);

        $otherModel = TestModel::create([
            "id" => "test-id",
        ]);

        if (!($otherModel instanceof TestModel)) {
            $this->fail("Failed to create TestModel instance.");
        }

        self::assertTrue($model->equals($otherModel));
    }

    public function testEqualsReturnsFalseForModelsWithDifferentClass(): void
    {
        $model = TestModel::create([
            "id" => "test-id",
        ]);

        $otherModel = AnotherTestModel::create([
            "id" => "test-id",
        ]);

        if (!($otherModel instanceof AnotherTestModel)) {
            $this->fail("Failed to create AnotherTestModel instance.");
        }

        self::assertFalse($model->equals($otherModel));
    }

    public function testEqualsReturnsFalseForModelsWithDifferentId(): void
    {
        $model = TestModel::create([
            "id" => "test-id",
        ]);

        $otherModel = TestModel::create([
            "id" => "another-id",
        ]);

        if (!($otherModel instanceof TestModel)) {
            $this->fail("Failed to create TestModel instance.");
        }

        self::assertFalse($model->equals($otherModel));
    }
}

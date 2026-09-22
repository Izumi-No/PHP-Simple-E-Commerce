<?php
declare(strict_types=1);
namespace Tests\Unit\Products;

use Izumi\Backend\app\Models\Product\Product;
use PHPUnit\Framework\TestCase;

class ProductModelTest extends TestCase
{
    private ?Product $product;

    public function setUp()
    {
        $product = Product::create(data: [
            "name" => "Test Product",
            "price" => 10.0,
            "description" => "Test Description",
            "quantity" => 5,
            "image_url" => "https://example.com/image.jpg"
        ]);
        if ($product instanceof Product) {
            $this->product = $product;
        } else {
            $this->fail("Failed to create product in setUp");
        }
    }

    public function testProductCreation()
    {
        $this->assertInstanceOf(Product::class, $this->product);
        $this->assertEquals("Test Product", $this->product->name);
        $this->assertEquals(10.0, $this->product->price);
        $this->assertEquals("Test Description", $this->product->description);
        $this->assertEquals(5, $this->product->quantity);
        $this->assertEquals("https://example.com/image.jpg", $this->product->image_url);
    }

    public function testProductCreationWithInvalidData()
    {
        $product = Product::create(data: [
            "name" => "",
            "price" => -10.0,
            "description" => "Test Description",
            "quantity" => -5,
            "image_url" => "invalid-url"
        ]);

        $this->assertIsArray($product);
        $this->assertContainsOnlyInstancesOf(\Izumi\Backend\app\Shared\Error::class, $product);
    }

    public function testProductReconstruction()
    {
        $reconstructedProduct = Product::fromArray(
            [
                "id" => $this->product->id,
                "name" => $this->product->name,
                "price" => $this->product->price,
                "description" => $this->product->description,
                "quantity" => $this->product->quantity,
                "image_url" => $this->product->image_url
            ]
        );

        $this->assertInstanceOf(Product::class, $reconstructedProduct);
        $this->assertEquals($this->product->id, $reconstructedProduct->id);
        $this->assertEquals($this->product->name, $reconstructedProduct->name);
        $this->assertEquals($this->product->price, $reconstructedProduct->price);
        $this->assertEquals($this->product->description, $reconstructedProduct->description);
        $this->assertEquals($this->product->quantity, $reconstructedProduct->quantity);
        $this->assertEquals($this->product->image_url, $reconstructedProduct->image_url);
    }



}

?>

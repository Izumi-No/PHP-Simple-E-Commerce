<?php

namespace Tests\Unit\Products;

use Izumi\Backend\app\Controllers\ProductsController;
use Izumi\Backend\app\Repositories\Products\InMemoryProductsRepository;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use PHPUnit\Framework\TestCase;

final class ProductsControllerTest extends TestCase
{
    private InMemoryProductsRepository $repository;
    private ProductsController $controller;

    protected function setUp(): void
    {
        $this->repository = new InMemoryProductsRepository();
        $this->controller = new ProductsController(
            $this->repository
        );
    }

    public function testCreateProduct(): void
    {
        $request = $this->request(
            method: 'POST',
            body: [
                'name' => 'Product 1',
                'price' => 10.0,
                'description' => 'Description 1',
                'quantity' => 5,
                'image_url' => 'https://example.com/image1.jpg',
            ]
        );

        $response = $this->controller->create($request);

        $this->assertSame(201, $response->status);
        $this->assertSame(
            'Product created successfully',
            $response->body['message']
        );

        $this->assertCount(1, $this->repository->findAll());

        $product = $this->repository->findAll()[0];

        $this->assertSame('Product 1', $product->name);
        $this->assertSame(10.0, $product->price);
        $this->assertSame(5, $product->quantity);
    }

    public function testCreateProductWithEmptyBody(): void
    {
        $request = $this->request(
            method: 'POST',
            body: ''
        );

        $response = $this->controller->create($request);

        $this->assertSame(400, $response->status);
        $this->assertSame(
            'Request body is empty',
            $response->body['error']
        );

        $this->assertCount(
            0,
            $this->repository->findAll()
        );
    }

    public function testCreateProductWithInvalidJson(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/products',
            body: '{invalid-json'
        );

        $response = $this->controller->create($request);

        $this->assertSame(400, $response->status);
        $this->assertSame(
            'Invalid JSON format',
            $response->body['error']
        );

        $this->assertCount(
            0,
            $this->repository->findAll()
        );
    }

    public function testCreateProductWithInvalidData(): void
    {
        $request = $this->request(
            method: 'POST',
            body: [
                'name' => '',
                'price' => -10.0,
                'description' => 'Invalid product',
                'quantity' => -1,
                'image_url' => 'invalid-url',
            ]
        );

        $response = $this->controller->create($request);

        $this->assertSame(422, $response->status);
        $this->assertArrayHasKey(
            'errors',
            $response->body
        );

        $this->assertCount(
            0,
            $this->repository->findAll()
        );
    }

    public function testGetAllProducts(): void
    {
        $this->createProduct();

        $request = new Request(
            method: 'GET',
            uri: '/products'
        );

        $response = $this->controller->getAll($request);

        $this->assertSame(200, $response->status);

        $this->assertArrayHasKey(
            'data',
            $response->body
        );

        $this->assertCount(
            1,
            $response->body['data']
        );

        $this->assertSame(
            'Product 1',
            $response->body['data'][0]['name']
        );
    }

    public function testGetProductById(): void
    {
        $product = $this->createProduct();

        $data = $product->toArray();

        $request = new Request(
            method: 'GET',
            uri: '/products/' . $data['id'],
            params: [
                'id' => $data['id'],
            ]
        );

        $response = $this->controller->getById($request);

        $this->assertSame(200, $response->status);

        $this->assertSame(
            $data['id'],
            $response->body['data']['id']
        );

        $this->assertSame(
            'Product 1',
            $response->body['data']['name']
        );
    }

    public function testGetProductByIdReturnsNotFound(): void
    {
        $request = new Request(
            method: 'GET',
            uri: '/products/non-existent-id',
            params: [
                'id' => 'non-existent-id',
            ]
        );

        $response = $this->controller->getById($request);

        $this->assertSame(404, $response->status);

        $this->assertSame(
            'Product not found',
            $response->body['error']
        );
    }

    public function testUpdateProduct(): void
    {
        $product = $this->createProduct();
        $id = $product->toArray()['id'];

        $request = $this->request(
            method: 'PUT',
            body: [
                'name' => 'Updated Product 1',
                'price' => 15.0,
                'description' => 'Updated Description 1',
                'quantity' => 10,
                'image_url' => 'https://example.com/image1_updated.jpg',
            ],
            params: [
                'id' => $id,
            ]
        );

        $response = $this->controller->update($request);

        $this->assertSame(200, $response->status);

        $this->assertSame(
            'Product updated successfully',
            $response->body['message']
        );

        $updated = $this->repository->findById($id);

        $this->assertNotNull($updated);
        $this->assertSame(
            'Updated Product 1',
            $updated->name
        );
        $this->assertSame(
            15.0,
            $updated->price
        );
        $this->assertSame(
            10,
            $updated->quantity
        );
    }

    public function testUpdateProductReturnsNotFound(): void
    {
        $request = $this->request(
            method: 'PUT',
            body: [
                'name' => 'Updated Product',
                'price' => 15.0,
                'description' => 'Updated Description',
                'quantity' => 10,
                'image_url' => 'https://example.com/image.jpg',
            ],
            params: [
                'id' => 'non-existent-id',
            ]
        );

        $response = $this->controller->update($request);

        $this->assertSame(404, $response->status);

        $this->assertSame(
            'Product not found',
            $response->body['error']
        );
    }

    public function testUpdateProductWithInvalidJson(): void
    {
        $product = $this->createProduct();
        $id = $product->toArray()['id'];

        $request = new Request(
            method: 'PUT',
            uri: '/products/' . $id,
            body: '{invalid-json',
            params: [
                'id' => $id,
            ]
        );

        $response = $this->controller->update($request);

        $this->assertSame(400, $response->status);

        $this->assertSame(
            'Invalid JSON format',
            $response->body['error']
        );
    }

    /**
     * @param array<string, mixed>|string $body
     * @param array<string, string> $params
     */
    private function request(
        string $method,
        array|string $body = '',
        array $params = []
    ): Request {
        return new Request(
            method: $method,
            uri: '/',
            body: is_array($body)
                ? json_encode(
                    $body,
                    JSON_THROW_ON_ERROR
                )
                : $body,
            params: $params,
        );
    }

    private function createProduct(): \Izumi\Backend\app\Models\Product\Product
    {
        $request = $this->request(
            method: 'POST',
            body: [
                'name' => 'Product 1',
                'price' => 10.0,
                'description' => 'Description 1',
                'quantity' => 5,
                'image_url' => 'https://example.com/image1.jpg',
            ]
        );

        $response = $this->controller->create($request);

        $this->assertSame(201, $response->status);

        $products = $this->repository->findAll();

        $this->assertCount(1, $products);

        return $products[0];
    }
}

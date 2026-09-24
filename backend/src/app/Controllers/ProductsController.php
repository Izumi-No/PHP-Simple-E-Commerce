<?php

namespace Izumi\Backend\app\Controllers;

use Izumi\Backend\app\DTOs\Product\ProductItemResponseDTO;
use Izumi\Backend\app\DTOs\Product\ProductCollectionResponseDTO;
use Izumi\Backend\app\DTOs\Product\ProductRequestDTO;
use Izumi\Backend\app\Models\Product\Product;
use Izumi\Backend\app\Repositories\Products\IProductsRepository;
use Izumi\Backend\app\Shared\Attributes\ApiOperation;
use Izumi\Backend\app\Shared\Attributes\ApiResponse;
use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Errors\ValidationError;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use JsonException;

final class ProductsController extends Controller
{
    public function __construct(
        private readonly IProductsRepository $repository,
    ) {}

    #[ApiOperation(summary: 'Create a new product', request: ProductRequestDTO::class, tags: ['products'])]
    #[ApiResponse(status: 201, description: 'Product created successfully', dto: ProductItemResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 422, description: 'Invalid product data')]
    public function create(Request $request): Response
    {
        if ($request->body === '') {
            return $this->response(
                ['error' => 'Request body is empty'],
                400
            );
        }

        try {
            $result = $request->validate(ProductRequestDTO::class);
        } catch (JsonException) {
            return $this->response(
                ['error' => 'Invalid JSON format'],
                400
            );
        }

        if ($result->isInvalid()) {
            return $this->response(
                ['errors' => self::mapValidationErrors($result->errors)],
                422
            );
        }

        $product = Product::create($result->value()->toArray());

        if (is_array($product)) {
            return $this->response(
                ['errors' => self::mapErrors($product)],
                422
            );
        }

        $this->repository->save($product);

        return $this->response(
            [
                'message' => 'Product created successfully',
                'data' => $product->toArray(),
            ],
            201
        );
    }

    #[ApiOperation(summary: 'List all products', tags: ['products'])]
    #[ApiResponse(status: 200, description: 'List of products', dto: ProductCollectionResponseDTO::class)]
    public function getAll(): Response
    {
        $products = $this->repository->findAll();

        return $this->response([
            'data' => array_map(
                static fn (Product $product): array => $product->toArray(),
                $products
            ),
        ]);
    }

    #[ApiOperation(summary: 'Get a single product by id', tags: ['products'])]
    #[ApiResponse(status: 200, description: 'Product found', dto: ProductItemResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Product ID is required')]
    #[ApiResponse(status: 404, description: 'Product not found')]
    public function getById(Request $request): Response
    {
        $id = $request->params['id'] ?? '';

        if ($id === '') {
            return $this->response(
                ['error' => 'Product ID is required'],
                400
            );
        }

        $product = $this->repository->findById($id);

        if ($product === null) {
            return $this->response(
                ['error' => 'Product not found'],
                404
            );
        }

        return $this->response([
            'data' => $product->toArray(),
        ]);
    }

    #[ApiOperation(summary: 'Update an existing product', request: ProductRequestDTO::class, tags: ['products'])]
    #[ApiResponse(status: 200, description: 'Product updated successfully', dto: ProductItemResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 404, description: 'Product not found')]
    #[ApiResponse(status: 422, description: 'Invalid product data')]
    public function update(Request $request): Response
    {
        $id = $request->params['id'] ?? '';

        if ($id === '') {
            return $this->response(
                ['error' => 'Product ID is required'],
                400
            );
        }

        if ($request->body === '') {
            return $this->response(
                ['error' => 'Request body is empty'],
                400
            );
        }

        try {
            $data = $request->json();
        } catch (JsonException) {
            return $this->response(
                ['error' => 'Invalid JSON format'],
                400
            );
        }

        $product = $this->repository->findById($id);

        if ($product === null) {
            return $this->response(
                ['error' => 'Product not found'],
                404
            );
        }

        $errors = $product->update($data);

        if ($errors !== []) {
            return $this->response(
                ['errors' => self::mapErrors($errors)],
                422
            );
        }

        $this->repository->update($id, $data);

        return $this->response([
            'message' => 'Product updated successfully',
            'data' => $product->toArray(),
        ]);
    }

    #[ApiOperation(summary: 'Delete a product', tags: ['products'])]
    #[ApiResponse(status: 200, description: 'Product deleted successfully')]
    #[ApiResponse(status: 400, description: 'Product ID is required')]
    #[ApiResponse(status: 404, description: 'Product not found')]
    public function delete(Request $request): Response
    {
        $id = $request->params['id'] ?? '';

        if ($id === '') {
            return $this->response(
                ['error' => 'Product ID is required'],
                400
            );
        }

        $product = $this->repository->findById($id);

        if ($product === null) {
            return $this->response(
                ['error' => 'Product not found'],
                404
            );
        }

        $this->repository->delete($id);

        return $this->response([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * @param list<Error> $errors
     *
     * @return list<array{code: string, message: string}>
     */
    private static function mapErrors(array $errors): array
    {
        return array_map(
            static fn (Error $error): array => $error->toArray(),
            $errors
        );
    }

    /**
     * @param list<ValidationError> $errors
     *
     * @return list<array{field: string, code: string, message: string}>
     */
    private static function mapValidationErrors(array $errors): array
    {
        return array_map(
            static fn (ValidationError $error): array => $error->toArray(),
            $errors
        );
    }
}

<?php

namespace Izumi\Backend\app\Controllers;

use Izumi\Backend\app\Models\Product\Product;
use Izumi\Backend\app\Repositories\Products\IProductsRepository;
use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use JsonException;

use function Izumi\Backend\app\Shared\map_errors_to_arrays;

final class ProductsController extends Controller
{
    public function __construct(IProductsRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(Request $request): Response
    {
        $body = $request->body;

        if ($body === '') {
            return $this->response(
                ['error' => 'Request body is empty'],
                400
            );
        }

        try {
            $data = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return $this->response(
                ['error' => 'Invalid JSON format'],
                400
            );
        }

        $product = Product::create($data);

        if (is_array($product)) {
            return $this->response(
                ['errors' => map_errors_to_arrays($product)],
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

    public function update(Request $request): Response
    {
        $id = $request->params['id'] ?? '';
        $body = $request->body;

        if ($id === '') {
            return $this->response(
                ['error' => 'Product ID is required'],
                400
            );
        }

        if ($body === '') {
            return $this->response(
                ['error' => 'Request body is empty'],
                400
            );
        }

        try {
            $data = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
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
                ['errors' => map_errors_to_arrays($errors)],
                422
            );
        }

        $this->repository->update($id, $data);

        return $this->response([
            'message' => 'Product updated successfully',
            'data' => $product->toArray(),
        ]);
    }

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
}

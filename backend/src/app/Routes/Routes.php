<?php

use Izumi\Backend\app\Controllers\ProductsController;
use Izumi\Backend\app\Shared\Router;

return function (
    Router $router,
    ProductsController $productsController
): void {
    $router->get(
        '/products',
        [$productsController, 'getAll']
    );

    $router->get(
        '/products/{id}',
        [$productsController, 'getById']
    );

    $router->post(
        '/products',
        [$productsController, 'create']
    );

    $router->put(
        '/products/{id}',
        [$productsController, 'update']
    );

    $router->delete(
        '/products/{id}',
        [$productsController, 'delete']
    );
};

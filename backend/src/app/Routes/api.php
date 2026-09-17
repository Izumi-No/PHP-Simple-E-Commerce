<?php
use Izumi\Backend\app\Controllers\ProductsController;
use Izumi\Backend\app\Shared\Router;



function api_routes(Router $router, ProductsController $productController): Router
{

    $router->get('/products', fn() => $productController->getAll());
    $getProductById = function ($request) use ($productController) {
        return $productController->getById($request);
    };
    $router->get('/products/{id}', function ($request) use ($productController) {

            return $productController->getById($request);
        });

    $router->post('/products', function ($request) use ($productController) {
        return $productController->create($request);
    });
    $router->put('/products/{id}',
    function ($request) use ($productController) {
        return $productController->update($request);
    }
    );
    $router->delete('/products/{id}',
    function ($request) use ($productController) {
        return $productController->delete($request);
    }

    );

    return $router;
}

?>

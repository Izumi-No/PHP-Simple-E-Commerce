<?php

use Izumi\Backend\app\Controllers\Shops\ShopsController;
use Izumi\Backend\app\Shared\Router;

return function (Router $router, ShopsController $shopsController): void {
    $router->get('/shops', [$shopsController, 'getAll']);

    $router->post('/shops', [$shopsController, 'create']);

    $router->post('/shops/{shopId}/tags', [$shopsController, 'createTag']);

    $router->delete('/shops/{shopId}/tags/{tagId}', [$shopsController, 'deleteTag']);
};

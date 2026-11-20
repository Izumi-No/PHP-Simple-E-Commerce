<?php

use Izumi\Backend\app\Controllers\Collectables\CollectablesController;
use Izumi\Backend\app\Shared\Router;

return function (Router $router, CollectablesController $collectablesController): void {
    $router->get('/collectables', [$collectablesController, 'getAll']);
    $router->get('/collectables/{id}', [$collectablesController, 'getById']);
    $router->post('/collectables', [$collectablesController, 'create']);
    $router->put('/collectables/{id}', [$collectablesController, 'update']);
    $router->delete('/collectables/{id}', [$collectablesController, 'delete']);

    $router->post('/collectables/{id}/tags', [$collectablesController, 'associateTag']);
    $router->put('/collectables/{id}/tags', [$collectablesController, 'replaceTags']);
    $router->delete('/collectables/{id}/tags/{tagId}', [$collectablesController, 'removeTag']);

    $router->get('/shops/{shopId}/collectables', [$collectablesController, 'getShopCollectables']);
};

<?php

use Izumi\Backend\app\Controllers\Auth\AuthController;
use Izumi\Backend\app\Shared\Router;

return function (Router $router, AuthController $authController): void {
    $router->post('/auth/register', [$authController, 'register']);

    $router->post('/auth/verify', [$authController, 'verify']);

    $router->post('/auth/login', [$authController, 'login']);

    $router->post('/auth/logout', [$authController, 'logout']);

    $router->get('/auth/me', [$authController, 'me']);
};

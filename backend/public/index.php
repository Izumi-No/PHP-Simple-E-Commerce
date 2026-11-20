<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Izumi\Backend\app\Controllers\Shops\ShopsController;
use Izumi\Backend\app\Repositories\Sessions\PostgresSessionsRepository;
use Izumi\Backend\app\Repositories\Shops\PostgresShopsRepository;
use Izumi\Backend\app\Repositories\Tags\PostgresTagsRepository;
use Izumi\Backend\app\Repositories\TempUsers\PostgresTempUsersRepository;
use Izumi\Backend\app\Repositories\UserIdentities\PostgresUserIdentitiesRepository;
use Izumi\Backend\app\Repositories\Users\PostgresUsersRepository;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\OpenAPI\OpenApiGenerator;
use Izumi\Backend\app\Shared\OpenAPI\SwaggerUI;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use Izumi\Backend\app\Shared\Router;

$router = new Router();

$pdo = require __DIR__ . '/../src/app/Database/postgres.php';

// Auth dependencies
$usersRepository = new PostgresUsersRepository($pdo);
$identitiesRepository = new PostgresUserIdentitiesRepository($pdo);
$sessionsRepository = new PostgresSessionsRepository($pdo);
$tempUsersRepository = new PostgresTempUsersRepository($pdo);
$sessionResolver = new SessionResolver($sessionsRepository, $usersRepository);
$authController = new AuthController(
    $usersRepository,
    $identitiesRepository,
    $sessionsRepository,
    $tempUsersRepository,
    $sessionResolver,
);

// Shops and Tags dependencies
$shopsRepository = new PostgresShopsRepository($pdo);
$tagsRepository = new PostgresTagsRepository($pdo);
$shopsController = new ShopsController($shopsRepository, $tagsRepository, $sessionResolver);

$auth_routes = require __DIR__ . '/../src/app/Routes/AuthRoutes.php';
$shops_routes = require __DIR__ . '/../src/app/Routes/ShopsRoutes.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? null;
$allowedOrigins = ['*'];
if ($origin !== null && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$auth_routes($router, $authController);
$shops_routes($router, $shopsController);

$openApiGenerator = new OpenApiGenerator();

$router->get('/openapi.json', function () use ($router, $openApiGenerator): Response {
    return new Response($openApiGenerator->generate($router->routes()));
});

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/docs') {
    header('Content-Type: text/html; charset=utf-8');
    echo SwaggerUI::html('/openapi.json');
    exit();
}

$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

$response->send();

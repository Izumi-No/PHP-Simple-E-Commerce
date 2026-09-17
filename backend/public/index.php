<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Izumi\Backend\app\Controllers\ProductsController;
use Izumi\Backend\app\Repositories\Products\InMemoryProductsRepository;
use Izumi\Backend\app\Repositories\Products\PostgresProductsRepository;
use Izumi\Backend\app\Shared\Router;

$router = new Router();

$pdo = require __DIR__ . "/../src/app/Database/postgres.php";
$repository = new PostgresProductsRepository($pdo);
$productController = new ProductsController($repository);

$routes = require __DIR__ . "/../src/app/Routes/Routes.php";

$origin = $_SERVER["HTTP_ORIGIN"] ?? null;
$allowedOrigins = ["http://localhost:1234", "http://localhost:5173"];
if ($origin !== null && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit();
}

$routes($router, $productController);

$response = $router->dispatch(
    $_SERVER["REQUEST_METHOD"],
    $_SERVER["REQUEST_URI"],
);

$response->send();

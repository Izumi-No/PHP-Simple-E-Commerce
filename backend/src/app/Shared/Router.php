<?php

namespace Izumi\Backend\app\Shared;

final class Router
{
    /** @var array<int, array{
     *     method: string,
     *     path: string,
     *     handler: callable
     * }>
     */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(
        string $method,
        string $path,
        callable $handler
    ): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(
        string $method,
        string $uri
    ): Response {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $parameters = $this->match(
                $route['path'],
                $path
            );

            if ($parameters === null) {
                continue;
            }

            $request = new Request(
                method: strtoupper($method),
                uri: $uri,
                body: file_get_contents('php://input') ?: '',
                query: $_GET,
                params: $parameters,
            );

            $response = ($route['handler'])($request);

            if (!$response instanceof Response) {
                throw new \LogicException(
                    'Route handler must return a Response.'
                );
            }

            return $response;
        }

        return new Response(
            ['error' => 'Route not found'],
            404
        );
    }

    private function match(
        string $route,
        string $path
    ): ?array {
        $routeParts = $this->splitPath($route);
        $pathParts = $this->splitPath($path);

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $parameters = [];

        foreach ($routeParts as $index => $part) {
            $pathPart = $pathParts[$index];

            if (
                str_starts_with($part, '{') &&
                str_ends_with($part, '}')
            ) {
                $name = trim($part, '{}');
                $parameters[$name] = $pathPart;

                continue;
            }

            if ($part !== $pathPart) {
                return null;
            }
        }

        return $parameters;
    }

    private function splitPath(string $path): array
    {
        if ($path === '/') {
            return [];
        }

        return explode('/', trim($path, '/'));
    }
}

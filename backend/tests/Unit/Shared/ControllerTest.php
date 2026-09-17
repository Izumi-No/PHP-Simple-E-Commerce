<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Response;
use PHPUnit\Framework\TestCase;

final class TestController extends Controller
{
    public function makeResponse(
        mixed $data,
        int $status = 200,
        array $headers = [],
    ): Response {
        return $this->response(
            data: $data,
            status: $status,
            headers: $headers,
        );
    }
}

final class ControllerTest extends TestCase
{
    public function testStoresRepository(): void
    {
        $repository = new \stdClass();

        $controller = new TestController($repository);

        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('repository');

        self::assertSame(
            $repository,
            $property->getValue($controller),
        );
    }

    public function testResponseUsesDefaultStatusAndHeaders(): void
    {
        $controller = new TestController(new \stdClass());

        $response = $controller->makeResponse([
            'message' => 'OK',
        ]);

        self::assertSame(
            ['message' => 'OK'],
            $response->body,
        );

        self::assertSame(200, $response->status);

        self::assertSame(
            ['Content-Type' => 'application/json'],
            $response->headers,
        );
    }

    public function testResponseUsesCustomStatus(): void
    {
        $controller = new TestController(new \stdClass());

        $response = $controller->makeResponse(
            data: ['error' => 'Not Found'],
            status: 404,
        );

        self::assertSame(404, $response->status);
        self::assertSame(
            ['Content-Type' => 'application/json'],
            $response->headers,
        );
    }

    public function testResponseUsesCustomHeaders(): void
    {
        $controller = new TestController(new \stdClass());

        $headers = [
            'Content-Type' => 'text/plain',
            'X-Custom-Header' => 'test',
        ];

        $response = $controller->makeResponse(
            data: 'Hello',
            headers: $headers,
        );

        self::assertSame($headers, $response->headers);
    }

    public function testResponseUsesDefaultHeadersWhenEmptyHeadersAreProvided(): void
    {
        $controller = new TestController(new \stdClass());

        $response = $controller->makeResponse(
            data: [],
            headers: [],
        );

        self::assertSame(
            ['Content-Type' => 'application/json'],
            $response->headers,
        );
    }
}

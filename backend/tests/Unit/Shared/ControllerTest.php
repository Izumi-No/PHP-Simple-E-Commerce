<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Response;
use PHPUnit\Framework\TestCase;

final readonly class TestControllerError extends Error
{
}

final class TestController extends Controller
{
    public function makeResponse(
        mixed $data,
        int $status = 200,
    ): Response {
        return $this->response(
            data: $data,
            status: $status,
        );
    }

    public function makeError(Error $error, int $status): Response
    {
        return $this->error($error, $status);
    }
}

final class ControllerTest extends TestCase
{
    public function testResponseUsesDefaultStatusAndHeaders(): void
    {
        $controller = new TestController();

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
        $controller = new TestController();

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

    public function testErrorReturnsErrorResponse(): void
    {
        $controller = new TestController();

        $response = $controller->makeError(
            new TestControllerError(
                code: 'InvalidName',
                message: 'Name cannot be empty',
            ),
            422,
        );

        self::assertSame(422, $response->status);
        self::assertSame(
            [
                'error' => [
                    'code' => 'InvalidName',
                    'message' => 'Name cannot be empty',
                ],
            ],
            $response->body,
        );
    }
}
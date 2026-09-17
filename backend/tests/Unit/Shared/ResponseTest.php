<?php
declare(strict_types=1);

namespace Tests\Unit\Shared;


use Izumi\Backend\app\Shared\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testCreatesResponseWithDefaultValues(): void
    {
        $response = new Response('Hello');

        self::assertSame('Hello', $response->body);
        self::assertSame(200, $response->status);
        self::assertSame(
            ['Content-Type' => 'application/json'],
            $response->headers,
        );
    }

    public function testCreatesResponseWithCustomStatus(): void
    {
        $response = new Response(
            body: ['message' => 'Not Found'],
            status: 404,
        );

        self::assertSame(['message' => 'Not Found'], $response->body);
        self::assertSame(404, $response->status);
    }

    public function testCreatesResponseWithCustomHeaders(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Custom-Header' => 'custom-value',
        ];

        $response = new Response(
            body: ['message' => 'OK'],
            headers: $headers,
        );

        self::assertSame($headers, $response->headers);
    }

    public function testCreatesResponseWithCustomStatusAndHeaders(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Request-ID' => 'test-id',
        ];

        $response = new Response(
            body: ['error' => 'Invalid request'],
            status: 422,
            headers: $headers,
        );

        self::assertSame(['error' => 'Invalid request'], $response->body);
        self::assertSame(422, $response->status);
        self::assertSame($headers, $response->headers);
    }

    public function testSendOutputsJsonEncodedBody(): void
    {
        $response = new Response([
            'message' => 'Hello',
        ]);

        ob_start();

        $response->send();

        $output = ob_get_clean();

        self::assertSame(
            '{"message":"Hello"}',
            $output,
        );
    }

    public function testSendOutputsJsonEncodedScalarBody(): void
    {
        $response = new Response('Hello');

        ob_start();

        $response->send();

        $output = ob_get_clean();

        self::assertSame('"Hello"', $output);
    }

    public function testSendOutputsJsonEncodedNestedData(): void
    {
        $body = [
            'user' => [
                'id' => 'test-id',
                'name' => 'Izumi',
            ],
            'items' => [1, 2, 3],
        ];

        $response = new Response($body);

        ob_start();

        $response->send();

        $output = ob_get_clean();

        self::assertSame(
            json_encode($body, JSON_THROW_ON_ERROR),
            $output,
        );
    }

    public function testSendSetsResponseStatusCode(): void
    {
        $response = new Response(
            body: ['error' => 'Not Found'],
            status: 404,
        );

        $response->send();

        self::assertSame(404, http_response_code());
    }
}

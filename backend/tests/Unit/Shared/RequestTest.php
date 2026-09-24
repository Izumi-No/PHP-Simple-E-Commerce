<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Request;
use JsonException;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCreatesRequestWithDefaultValues(): void
    {
        $request = new Request(
            method: 'GET',
            uri: '/users',
        );

        self::assertSame('GET', $request->method);
        self::assertSame('/users', $request->uri);
        self::assertSame('', $request->body);
        self::assertSame([], $request->query);
        self::assertSame([], $request->params);
    }

    public function testCreatesRequestWithCustomValues(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users?active=true',
            body: '{"name":"Izumi"}',
            query: ['active' => 'true'],
            params: ['id' => '123'],
        );

        self::assertSame('POST', $request->method);
        self::assertSame('/users?active=true', $request->uri);
        self::assertSame('{"name":"Izumi"}', $request->body);
        self::assertSame(['active' => 'true'], $request->query);
        self::assertSame(['id' => '123'], $request->params);
    }

    public function testJsonDecodesObject(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users',
            body: '{"name":"Izumi","age":21}',
        );

        self::assertSame(
            [
                'name' => 'Izumi',
                'age' => 21,
            ],
            $request->json(),
        );
    }

    public function testJsonDecodesNestedObject(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users',
            body: '{"user":{"name":"Izumi"},"roles":["admin","user"]}',
        );

        self::assertSame(
            [
                'user' => [
                    'name' => 'Izumi',
                ],
                'roles' => [
                    'admin',
                    'user',
                ],
            ],
            $request->json(),
        );
    }

    public function testJsonThrowsExceptionForInvalidJson(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users',
            body: '{"name":',
        );

        $this->expectException(JsonException::class);

        $request->json();
    }

    public function testJsonThrowsExceptionForNonObjectJson(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users',
            body: '"hello"',
        );

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage(
            'Request body must contain a JSON object.'
        );

        $request->json();
    }

    public function testJsonThrowsExceptionForJsonArray(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/users',
            body: '[1,2,3]',
        );

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage(
            'Request body must contain a JSON object.'
        );

        $request->json();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use Izumi\Backend\app\Shared\Router;
use LogicException;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testDispatchesGetRoute(): void
    {
        $router = new Router();

        $router->get('/users', function (Request $request): Response {
            return new Response([
                'method' => $request->method,
                'uri' => $request->uri,
            ]);
        });

        $response = $router->dispatch('GET', '/users');

        self::assertSame(200, $response->status);
        self::assertSame(
            [
                'method' => 'GET',
                'uri' => '/users',
            ],
            $response->body,
        );
    }

    public function testDispatchesPostRoute(): void
    {
        $router = new Router();

        $router->post('/users', function (): Response {
            return new Response(['created' => true], 201);
        });

        $response = $router->dispatch('POST', '/users');

        self::assertSame(201, $response->status);
        self::assertSame(
            ['created' => true],
            $response->body,
        );
    }

    public function testDispatchesPutRoute(): void
    {
        $router = new Router();

        $router->put('/users', function (): Response {
            return new Response(['updated' => true]);
        });

        $response = $router->dispatch('PUT', '/users');

        self::assertSame(
            ['updated' => true],
            $response->body,
        );
    }

    public function testDispatchesDeleteRoute(): void
    {
        $router = new Router();

        $router->delete('/users', function (): Response {
            return new Response(['deleted' => true]);
        });

        $response = $router->dispatch('DELETE', '/users');

        self::assertSame(
            ['deleted' => true],
            $response->body,
        );
    }

    public function testMethodMatchingIsCaseInsensitive(): void
    {
        $router = new Router();

        $router->get('/users', function (): Response {
            return new Response(['success' => true]);
        });

        $response = $router->dispatch('get', '/users');

        self::assertSame(200, $response->status);
        self::assertSame(
            ['success' => true],
            $response->body,
        );
    }

    public function testDispatchesRouteWithParameter(): void
    {
        $router = new Router();

        $router->get('/users/{id}', function (Request $request): Response {
            return new Response([
                'id' => $request->params['id'],
            ]);
        });

        $response = $router->dispatch('GET', '/users/123');

        self::assertSame(
            ['id' => '123'],
            $response->body,
        );
    }

    public function testDispatchesRouteWithMultipleParameters(): void
    {
        $router = new Router();

        $router->get(
            '/users/{userId}/posts/{postId}',
            function (Request $request): Response {
                return new Response([
                    'userId' => $request->params['userId'],
                    'postId' => $request->params['postId'],
                ]);
            },
        );

        $response = $router->dispatch(
            'GET',
            '/users/123/posts/456',
        );

        self::assertSame(
            [
                'userId' => '123',
                'postId' => '456',
            ],
            $response->body,
        );
    }

    public function testDispatchesRouteWithQueryString(): void
    {
        $_GET = [
            'page' => '2',
            'limit' => '10',
        ];

        $router = new Router();

        $router->get('/users', function (Request $request): Response {
            return new Response($request->query);
        });

        $response = $router->dispatch(
            'GET',
            '/users?page=2&limit=10',
        );

        self::assertSame(
            [
                'page' => '2',
                'limit' => '10',
            ],
            $response->body,
        );
    }

    public function testQueryStringDoesNotAffectRouteMatching(): void
    {
        $router = new Router();

        $router->get('/users', function (): Response {
            return new Response(['matched' => true]);
        });

        $response = $router->dispatch(
            'GET',
            '/users?page=2',
        );

        self::assertSame(
            ['matched' => true],
            $response->body,
        );
    }

    public function testReturnsNotFoundWhenRouteDoesNotExist(): void
    {
        $router = new Router();

        $response = $router->dispatch(
            'GET',
            '/unknown',
        );

        self::assertSame(404, $response->status);
        self::assertSame(
            ['error' => 'Route not found'],
            $response->body,
        );
    }

    public function testReturnsNotFoundWhenMethodDoesNotMatch(): void
    {
        $router = new Router();

        $router->get('/users', function (): Response {
            return new Response(['matched' => true]);
        });

        $response = $router->dispatch(
            'POST',
            '/users',
        );

        self::assertSame(404, $response->status);
        self::assertSame(
            ['error' => 'Route not found'],
            $response->body,
        );
    }

    public function testDoesNotMatchRouteWithDifferentNumberOfSegments(): void
    {
        $router = new Router();

        $router->get('/users/{id}', function (): Response {
            return new Response(['matched' => true]);
        });

        $response = $router->dispatch(
            'GET',
            '/users/123/profile',
        );

        self::assertSame(404, $response->status);
    }

    public function testDoesNotMatchDifferentStaticPath(): void
    {
        $router = new Router();

        $router->get('/users', function (): Response {
            return new Response(['matched' => true]);
        });

        $response = $router->dispatch(
            'GET',
            '/products',
        );

        self::assertSame(404, $response->status);
    }

    public function testHandlerMustReturnResponse(): void
    {
        $router = new Router();

        $router->get('/users', function (): string {
            return 'invalid response';
        });

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains(
            'Route handler must return a Response.'
        );

        $router->dispatch('GET', '/users');
    }

    protected function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }
}

<?php

namespace Izumi\Backend\app\Shared\OpenAPI;

use Izumi\Backend\app\Shared\Attributes\ApiOperation;
use Izumi\Backend\app\Shared\Attributes\ApiResponse;
use ReflectionClass;
use ReflectionMethod;

final class OpenApiGenerator
{
    public function __construct(
        private readonly SchemaGenerator $schemas = new SchemaGenerator(),
    ) {}

    /**
     * @param list<array{
     *     method: string,
     *     path: string,
     *     handler: callable
     * }> $routes
     */
    public function generate(array $routes): array
    {
        $paths = [];

        foreach ($routes as $route) {
            $handler = $route['handler'];

            if (!is_array($handler) || !isset($handler[0]) || !is_object($handler[0])) {
                continue;
            }

            [$controller, $method] = $handler;

            $reflection = new ReflectionMethod($controller, $method);

            $operationAttribute = $reflection->getAttributes(ApiOperation::class)[0] ?? null;

            if ($operationAttribute === null) {
                continue;
            }

            $operation = $operationAttribute->newInstance();

            $responses = [];

            foreach ($reflection->getAttributes(ApiResponse::class) as $attribute) {
                $response = $attribute->newInstance();

                $content = [];

                if ($response->dto !== null) {
                    $this->schemas->generate($response->dto);

                    $content = [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/' . (new ReflectionClass($response->dto))->getShortName(),
                                ],
                            ],
                        ],
                    ];
                }

                $responses[(string) $response->status] = [
                    'description' => $response->description,
                    ...$content,
                ];
            }

            $requestBody = [];

            if ($operation->request !== null) {
                $this->schemas->generate($operation->request);

                $requestBody = [
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/' . (new ReflectionClass($operation->request))->getShortName(),
                                ],
                            ],
                        ],
                    ],
                ];
            }

            $paths[$route['path']][strtolower($route['method'])] = [
                'summary' => $operation->summary,
                'tags' => $operation->tags,
                ...$requestBody,
                'responses' => (object) $responses,
            ];
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Marketplace API',
                'version' => '1.0.0',
            ],
            'paths' => (object) $paths,
            'components' => [
                'schemas' => (object) $this->namedSchemas($this->schemas->all()),
            ],
        ];
    }

    /**
     * @param array<class-string<\Izumi\Backend\app\Shared\DTO>, array<string, mixed>> $components
     *
     * @return array<string, array<string, mixed>>
     */
    private function namedSchemas(array $components): array
    {
        $schemas = [];

        foreach ($components as $class => $schema) {
            $schemas[(new ReflectionClass($class))->getShortName()] = $schema;
        }

        return $schemas;
    }
}
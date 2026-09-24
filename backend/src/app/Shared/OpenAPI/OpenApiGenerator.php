<?php

namespace Izumi\Backend\app\Shared\OpenAPI;

use Izumi\Backend\app\Shared\Attributes\ApiOperation;
use Izumi\Backend\app\Shared\Attributes\ApiResponse;
use Izumi\Backend\app\Shared\DTO;
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
     *     handler: array{0: object, 1: string}
     * }> $routes
     */
    public function generate(array $routes): array
    {
        $paths = [];
        $components = [];

        foreach ($routes as $route) {
            [$controller, $method] = $route['handler'];

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
                    $dto = $response->dto;

                    $components[$dto] = $this->schemas->generate($dto);

                    $content = [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/' . $dto,
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
                $dto = $operation->request;

                $components[$dto] = $this->schemas->generate($dto);

                $requestBody = [
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/' . $dto,
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
                'schemas' => $this->namedSchemas($components),
            ],
        ];
    }

    private function namedSchemas(array $components): array
    {
        $schemas = [];

        foreach ($components as $class => $schema) {
            $schemas[new \ReflectionClass($class)->getShortName()] = $schema;
        }

        return $schemas;
    }
}

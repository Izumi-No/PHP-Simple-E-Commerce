<?php

namespace Izumi\Backend\app\Shared;

/**
 * @template T
 */
abstract class Controller
{
    /** @var T */
    protected $repository;

    /** @param T $repository */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    protected function response(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): Response {
        return new Response(
            body: $data,
            status: $status,
            headers: $headers ?: [
                'Content-Type' => 'application/json',
            ],
        );
    }
}

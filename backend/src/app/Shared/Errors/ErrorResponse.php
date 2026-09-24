<?php

namespace Izumi\Backend\app\Shared\Errors;

use Izumi\Backend\app\Shared\Response;

final class ErrorResponse
{
    public static function from(string $code, string $message, int $status, array $details = []): Response
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $error['details'] = $details;
        }

        return new Response(body: ['error' => $error], status: $status);
    }

    public static function validation(array $errors): Response
    {
        return self::from(
            code: 'VALIDATION_ERROR',
            message: 'The request contains invalid fields.',
            status: 422,
            details: array_map(static fn(ValidationError $error): array => $error->toArray(), $errors),
        );
    }
}

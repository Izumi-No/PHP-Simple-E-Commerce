<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Errors\Error;
use PHPUnit\Framework\TestCase;

final readonly class TestError extends Error
{
}

final class ErrorTest extends TestCase
{
    public function testCreatesErrorWithProvidedValues(): void
    {
        $error = new TestError(
            code: 'InvalidName',
            message: 'Name cannot be empty',
        );

        self::assertSame('InvalidName', $error->code);
        self::assertSame('Name cannot be empty', $error->message);
    }

    public function testToArrayReturnsErrorData(): void
    {
        $error = new TestError(
            code: 'InvalidName',
            message: 'Name cannot be empty',
        );

        self::assertSame(
            [
                'code' => 'InvalidName',
                'message' => 'Name cannot be empty',
            ],
            $error->toArray(),
        );
    }

    public function testMapErrorsToArraysMapsAllErrors(): void
    {
        $errors = [
            new TestError(
                code: 'InvalidName',
                message: 'Name cannot be empty',
            ),
            new TestError(
                code: 'InvalidPrice',
                message: 'Price cannot be negative',
            ),
        ];

        self::assertSame(
            [
                [
                    'code' => 'InvalidName',
                    'message' => 'Name cannot be empty',
                ],
                [
                    'code' => 'InvalidPrice',
                    'message' => 'Price cannot be negative',
                ],
            ],
            array_map(
                static fn (Error $error): array => $error->toArray(),
                $errors
            ),
        );
    }

    public function testMapErrorsToArraysReturnsEmptyArrayForEmptyInput(): void
    {
        self::assertSame([], array_map(fn (Error $error): array => $error->toArray(), []));
    }
}
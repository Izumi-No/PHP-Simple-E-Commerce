<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Izumi\Backend\app\Shared\Error;
use PHPUnit\Framework\TestCase;

use function Izumi\Backend\app\Shared\map_errors_to_arrays;

final class TestError extends Error
{
}

final class ErrorTest extends TestCase
{
    public function testCreatesErrorWithProvidedValues(): void
    {
        $error = new TestError(
            name: 'InvalidName',
            message: 'Name cannot be empty',
            code: 1,
        );

        self::assertSame('InvalidName', $error->name);
        self::assertSame('Name cannot be empty', $error->message);
        self::assertSame(1, $error->code);
    }

    public function testToArrayReturnsErrorData(): void
    {
        $error = new TestError(
            name: 'InvalidName',
            message: 'Name cannot be empty',
            code: 1,
        );

        self::assertSame(
            [
                'name' => 'InvalidName',
                'message' => 'Name cannot be empty',
                'code' => 1,
            ],
            $error->toArray(),
        );
    }

    public function testMapErrorsToArraysMapsAllErrors(): void
    {
        $errors = [
            new TestError(
                name: 'InvalidName',
                message: 'Name cannot be empty',
                code: 1,
            ),
            new TestError(
                name: 'InvalidPrice',
                message: 'Price cannot be negative',
                code: 2,
            ),
        ];

        self::assertSame(
            [
                [
                    'name' => 'InvalidName',
                    'message' => 'Name cannot be empty',
                    'code' => 1,
                ],
                [
                    'name' => 'InvalidPrice',
                    'message' => 'Price cannot be negative',
                    'code' => 2,
                ],
            ],
            map_errors_to_arrays($errors),
        );
    }

    public function testMapErrorsToArraysReturnsEmptyArrayForEmptyInput(): void
    {
        self::assertSame([], map_errors_to_arrays([]));
    }
}

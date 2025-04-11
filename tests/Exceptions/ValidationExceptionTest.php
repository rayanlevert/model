<?php

namespace RayanLevert\Model\Tests\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Exceptions\ValidationException;

#[CoversClass(ValidationException::class)]
class ValidationExceptionTest extends TestCase
{
    #[Test]
    public function constructorWithSingleError(): void
    {
        $exception = new ValidationException('name is required');

        $this->assertSame(['name is required'], $exception->getErrors());
        $this->assertSame('name is required', $exception->getMessage());
    }

    #[Test]
    public function constructorWithMultipleErrors(): void
    {
        $exception = new ValidationException('name is required', 'age must be at least 18');

        $this->assertSame(['name is required', 'age must be at least 18'], $exception->getErrors());
        $this->assertSame('name is required, age must be at least 18', $exception->getMessage());
    }

    #[Test]
    #[DataProvider('errorsProvider')]
    public function constructorWithVariousErrors(array $errors, string $expectedMessage): void
    {
        $exception = new ValidationException(...$errors);

        $this->assertSame($errors, $exception->getErrors());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public static function errorsProvider(): array
    {
        return [
            'empty array' => [
                [],
                ''
            ],
            'single error' => [
                ['name is required'],
                'name is required'
            ],
            'multiple errors' => [
                ['name is required', 'age must be at least 18', 'email is invalid'],
                'name is required, age must be at least 18, email is invalid'
            ],
            'errors with special characters' => [
                ['name contains "quotes"', 'age < 18', 'email@example.com is invalid'],
                'name contains "quotes", age < 18, email@example.com is invalid'
            ]
        ];
    }

    #[Test]
    public function getErrorsReturnsArray(): void
    {
        $exception = new ValidationException('error 1', 'error 2', 'error 3');

        $errors = $exception->getErrors();

        $this->assertIsArray($errors);
        $this->assertCount(3, $errors);
        $this->assertSame('error 1', $errors[0]);
        $this->assertSame('error 2', $errors[1]);
        $this->assertSame('error 3', $errors[2]);
    }

    #[Test]
    public function getMessageReturnsFormattedString(): void
    {
        $exception = new ValidationException('error 1', 'error 2', 'error 3');

        $message = $exception->getMessage();

        $this->assertIsString($message);
        $this->assertSame('error 1, error 2, error 3', $message);
    }
}

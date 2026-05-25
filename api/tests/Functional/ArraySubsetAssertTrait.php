<?php

declare(strict_types=1);

namespace Tests\Functional;

use ArrayAccess;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

trait ArraySubsetAssertTrait
{
    /**
     * Убеждается, что $subset является подмножеством $array.
     *
     * @param array<mixed>|ArrayAccess<mixed, mixed> $subset
     * @param array<mixed>|ArrayAccess<mixed, mixed> $array
     *
     * @throws ExpectationFailedException
     */
    public static function assertArraySubset(
        array|ArrayAccess $subset,
        array|ArrayAccess $array,
        bool $checkForObjectIdentity = false,
        string $message = ''
    ): void {
        $errors = [];
        self::checkArraySubsetRecursive($subset, $array, $checkForObjectIdentity, [], $errors);

        if (\count($errors) > 0) {
            $errorDetails = implode("\n", $errors);
            $finalMessage = '' !== $message
                ? $message . "\n" . $errorDetails
                : "Failed asserting that array is a subset.\n" . $errorDetails;

            Assert::fail($finalMessage);
        } else {
            // Увеличиваем внутренний счетчик ассертов PHPUnit, чтобы тест не помечался как risky
            Assert::assertTrue(true);
        }
    }

    /**
     * @param array<mixed>|ArrayAccess<mixed, mixed> $subset
     * @param array<mixed>|ArrayAccess<mixed, mixed> $array
     * @param array<int, int|string> $path
     * @param array<int, string> $errors
     */
    private static function checkArraySubsetRecursive(
        array|ArrayAccess $subset,
        array|ArrayAccess $array,
        bool $checkForObjectIdentity,
        array $path,
        array &$errors
    ): void {
        foreach ($subset as $key => $value) {
            $currentPath = array_merge($path, [$key]);
            $pathString = '[' . implode('][', $currentPath) . ']';

            $exists = \is_array($array) ? \array_key_exists($key, $array) : $array->offsetExists($key);

            if (!$exists) {
                $errors[] = "- Missing key at path: {$pathString}";
                continue;
            }

            $arrayValue = $array[$key];

            if (\is_array($value) && \is_array($arrayValue)) {
                self::checkArraySubsetRecursive($value, $arrayValue, $checkForObjectIdentity, $currentPath, $errors);
                continue;
            }

            // Для конечных узлов используем родные ассерты PHPUnit
            try {
                if ($checkForObjectIdentity) {
                    Assert::assertSame($value, $arrayValue);
                } else {
                    Assert::assertEquals($value, $arrayValue);
                }
            } catch (ExpectationFailedException $e) {
                // Извлекаем только первую строку из Exception, чтобы вывод в консоли оставался читаемым
                $mismatchMessage = explode("\n", $e->getMessage())[0];
                $errors[] = "- Mismatch at path {$pathString}: {$mismatchMessage}";
            }
        }
    }
}

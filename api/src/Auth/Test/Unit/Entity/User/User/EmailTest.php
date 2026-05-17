<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\User;

use App\Auth\Entity\User\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class EmailTest extends TestCase
{
    public function testSuccess(): void
    {
        $email = new Email($value = 'email@app.test');

        self::assertSame($value, $email->value);
    }

    public function testCase(): void
    {
        $email = new Email('EmAil@app.test');

        self::assertSame('email@app.test', $email->value);
    }

    public function testIncorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('not-email');
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('');
    }
}

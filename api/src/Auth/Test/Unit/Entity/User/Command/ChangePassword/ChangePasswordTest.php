<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\ChangePassword;

use App\Auth\Event\PasswordChanged;
use App\Auth\Service\PasswordHasher;
use App\Auth\Test\Builder\UserBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ChangePasswordTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new UserBuilder()
            ->active()
            ->build();

        $hasher = $this->createHasher(true, $hash = 'new-hash');

        $user->changePassword(
            'old-password',
            'new-password',
            $hasher
        );

        self::assertEquals($hash, $user->getPasswordHash());

        $events = $user->releaseEvents();
        $event = end($events);

        self::assertInstanceOf(PasswordChanged::class, $event);
    }

    public function testWrongCurrent(): void
    {
        $user = new UserBuilder()
            ->active()
            ->build();

        $hasher = $this->createHasher(false, 'new-hash');

        $this->expectExceptionMessage('Incorrect current password.');
        $user->changePassword(
            'wrong-old-password',
            'new-password',
            $hasher
        );
    }

    public function testByNetwork(): void
    {
        $user = new UserBuilder()
            ->viaNetwork('google', '000001')
            ->build();

        $hasher = $this->createHasher(false, 'new-hash');

        $this->expectExceptionMessage('User does not have an old password.');
        $user->changePassword(
            'any-old-password',
            'new-password',
            $hasher
        );
    }

    private function createHasher(bool $valid, string $hash): PasswordHasher
    {
        $hasher = self::createStub(PasswordHasher::class);
        $hasher->method('validate')->willReturn($valid);
        $hasher->method('hash')->willReturn($hash);
        return $hasher;
    }
}

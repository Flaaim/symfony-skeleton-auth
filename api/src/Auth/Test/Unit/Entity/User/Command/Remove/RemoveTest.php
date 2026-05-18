<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\Remove;

use App\Auth\Event\UserRemoved;
use App\Auth\Test\Builder\UserBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class RemoveTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testSuccess(): void
    {
        $user = new UserBuilder()
            ->build();
        $user->remove();

        self::assertNotEmpty($events = $user->releaseEvents());
        $event = end($events);

        self::assertInstanceOf(UserRemoved::class, $event);
        self::assertEquals($user->getId()->getValue(), $event->id);
    }

    public function testActive(): void
    {
        $user = new UserBuilder()
            ->active()
            ->build();

        $this->expectExceptionMessage('Unable to remove active user.');

        $user->remove();
    }
}

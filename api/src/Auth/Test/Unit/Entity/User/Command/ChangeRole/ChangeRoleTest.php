<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\ChangeRole;

use App\Auth\Entity\User\Role;
use App\Auth\Event\UserRoleChanged;
use App\Auth\Test\Builder\UserBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ChangeRoleTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new UserBuilder()
            ->build();

        $user->changeRole($role = new Role(Role::ADMIN));

        self::assertEquals($role, $user->getRole());

        self::assertNotEmpty($events = $user->releaseEvents());

        $event = end($events);
        self::assertInstanceOf(UserRoleChanged::class, $event);
        self::assertEquals(Role::ADMIN, $event->role);
    }
}

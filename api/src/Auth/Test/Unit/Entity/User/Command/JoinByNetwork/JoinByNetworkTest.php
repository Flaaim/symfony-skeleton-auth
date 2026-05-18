<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\JoinByNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Network;
use App\Auth\Entity\User\Role;
use App\Auth\Entity\User\User;
use App\Auth\Event\UserCreated;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class JoinByNetworkTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = User::joinByNetwork(
            $id = Id::generate(),
            $date = new DateTimeImmutable(),
            $email = new Email('email@app.test'),
            $network = new Network('vk', '0000001')
        );

        self::assertEquals($id, $user->getId());
        self::assertEquals($date, $user->getDate());
        self::assertEquals($email, $user->getEmail());

        self::assertFalse($user->isWait());
        self::assertTrue($user->isActive());

        self::assertCount(1, $networks = $user->getNetworks());
        self::assertEquals($network, $networks[0] ?? null);

        self::assertEquals(Role::USER, $user->getRole()->getName());

        self::assertNotEmpty($events = $user->releaseEvents());
        $event = end($events);

        self::assertInstanceOf(UserCreated::class, $event);
        self::assertEquals($email->getValue(), $event->email);
    }
}

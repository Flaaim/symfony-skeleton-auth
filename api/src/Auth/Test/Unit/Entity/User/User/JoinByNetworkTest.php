<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\User;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Network;
use App\Auth\Entity\User\User;
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
            $name = 'vk',
            $identity = '0000001'
        );

        self::assertEquals($id, $user->getId());
        self::assertEquals($date, $user->getDate());
        self::assertEquals($email, $user->getEmail());

        self::assertFalse($user->isWait());
        self::assertTrue($user->isActive());

        self::assertCount(1, $networks = $user->getNetworks());
        /** @var array<Network> $networks */
        self::assertEquals($name, $networks[0]->getNetwork());
        self::assertEquals($identity, $networks[0]->getIdentity());
    }
}

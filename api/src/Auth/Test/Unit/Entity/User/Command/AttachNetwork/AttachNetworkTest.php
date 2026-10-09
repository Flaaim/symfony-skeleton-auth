<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\AttachNetwork;

use App\Auth\Entity\User\Network;
use App\Auth\Test\Builder\UserBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AttachNetworkTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new UserBuilder()
            ->active()
            ->build();

        $user->attachNetwork($name = 'vk', $identity = '0000001');

        self::assertCount(1, $networks = $user->getNetworks());

        self::assertEquals($name, $networks[0]->getNetwork());
        self::assertEquals($identity, $networks[0]->getIdentity());
    }

    public function testAlready(): void
    {
        $user = new UserBuilder()
            ->active()
            ->build();

        $user->attachNetwork($name = 'vk', $identity = '0000001');

        $this->expectExceptionMessage('Network is already attached.');
        $user->attachNetwork('vk', '0000001');
    }
}

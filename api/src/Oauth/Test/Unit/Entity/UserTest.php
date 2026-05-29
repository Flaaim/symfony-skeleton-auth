<?php

declare(strict_types=1);

namespace App\Oauth\Test\Unit\Entity;

use App\Oauth\Entity\User;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
final class UserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new User($identifier = Uuid::uuid4()->toString());

        self::assertSame($identifier, $user->getIdentifier());
    }
}

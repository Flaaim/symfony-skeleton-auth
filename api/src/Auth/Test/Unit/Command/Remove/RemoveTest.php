<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Command\Remove;

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

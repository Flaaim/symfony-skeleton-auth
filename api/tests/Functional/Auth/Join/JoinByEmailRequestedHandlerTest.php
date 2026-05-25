<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Join;

use App\Auth\Event\JoinByEmailRequested;
use App\Auth\MessageHandler\SendTokenOnJoinByEmailRequestedHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
final class JoinByEmailRequestedHandlerTest extends KernelTestCase
{
    public function testSuccess(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $handler = $container->get(SendTokenOnJoinByEmailRequestedHandler::class);

        $message = new JoinByEmailRequested('token-123', 'user@app.test');
        $handler($message);

        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(0), 'To', 'user@app.test');
    }
}

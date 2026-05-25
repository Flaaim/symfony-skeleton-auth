<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Event\ChangeEmailRequested;
use App\Auth\MessageHandler\SendTokenOnChangeEmailRequestedHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ChangeEmailRequestedHandlerTest extends KernelTestCase
{
    public function testSuccess(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $handler = $container->get(SendTokenOnChangeEmailRequestedHandler::class);
        $message = new ChangeEmailRequested('changed@email.ru', 'token-123');

        $handler($message);

        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(0), 'To', 'changed@email.ru');
    }
}

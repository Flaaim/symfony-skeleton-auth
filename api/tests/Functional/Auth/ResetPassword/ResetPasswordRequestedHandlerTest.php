<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ResetPassword;

use App\Auth\Event\PasswordResetRequested;
use App\Auth\MessageHandler\SendTokenOnResetPasswordHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
final class ResetPasswordRequestedHandlerTest extends KernelTestCase
{
    public function testSuccess(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $handler = $container->get(SendTokenOnResetPasswordHandler::class);

        $message = new PasswordResetRequested('user@test.ru', 'token-123');
        $handler($message);

        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(0), 'To', 'user@test.ru');
        self::assertEmailHtmlBodyContains(self::getMailerMessage(0), 'token-123');
    }
}

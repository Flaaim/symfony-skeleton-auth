<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Service;

use App\Auth\Entity\User\Email;
use App\Auth\Service\PasswordResetTokenSender;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 * @coversNothing
 */
final class PasswordResetTokenSenderTest extends TestCase
{
    public function testSuccess(): void
    {
        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();
        $template = 'auth/password/confirm.html.twig';
        $loader = new ArrayLoader([
            $template => "<a href='{{ link }}'>Ссылка</a>",
        ]);
        $twig = new Environment($loader);

        $symfonyEmail = new SymfonyEmail()
            ->to($to->getValue())
            ->subject('Password Reset')
            ->html($twig->render($template, ['token' => $token]));

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send')
            ->willReturnCallback(static function (SymfonyEmail $message) use ($symfonyEmail): int {
                self::assertEquals($symfonyEmail->getTo(), $message->getTo());
                self::assertEquals($symfonyEmail->getSubject(), $message->getSubject());
                self::assertStringContainsString($symfonyEmail->getHtmlBody(), $message->getHtmlBody());
                return 1;
            });

        $sender = new PasswordResetTokenSender($mailer, $twig);

        $sender->send($to, $token);
    }

    public function testError(): void
    {
        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();
        $template = 'auth/password/confirm.html.twig';
        $loader = new ArrayLoader([$template => "<a href='{{ link }}'>Ссылка</a>"]);
        $twig = new Environment($loader);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send')->willThrowException(new TransportException('Transport failed'));

        $sender = new PasswordResetTokenSender($mailer, $twig);

        $this->expectException(TransportException::class);
        $sender->send($to, $token);
    }
}

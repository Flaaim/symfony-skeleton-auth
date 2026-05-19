<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Service;

use App\Auth\Entity\User\Email;
use App\Auth\Service\NewEmailConfirmTokenSender;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class NewEmailConfirmTokenSenderTest extends TestCase
{
    public function testSuccess(): void
    {
        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();
        $template = 'auth/email/confirm.html.twig';
        $loader = new ArrayLoader([
            $template => "<a href='{{ link }}'>Ссылка</a>",
        ]);
        $twig = new Environment($loader);

        $symfonyEmail = new SymfonyEmail()
            ->to($to->getValue())
            ->subject('New Email Confirmation')
            ->html($twig->render($template, ['token' => $token]));

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')
            ->willReturnCallback(static function (SymfonyEmail $message) use ($symfonyEmail): int {
                self::assertEquals($symfonyEmail->getTo(), $message->getTo());
                self::assertEquals($symfonyEmail->getSubject(), $message->getSubject());
                self::assertStringContainsString($symfonyEmail->getHtmlBody(), $message->getHtmlBody());
                return 1;
            });

        $sender = new NewEmailConfirmTokenSender($mailer, $twig);

        $sender->send($to, $token);
    }

    public function testError(): void
    {
        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();
        $template = 'auth/email/confirm.html.twig';
        $loader = new ArrayLoader([$template => "<a href='{{ link }}'>Ссылка</a>",]);
        $twig = new Environment($loader);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send')->willThrowException(new TransportException('Transport failed'));

        $sender = new NewEmailConfirmTokenSender($mailer, $twig);

        $this->expectException(TransportException::class);
        $sender->send($to, $token);
    }
}

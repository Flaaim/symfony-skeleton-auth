<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Service;

use App\Auth\Entity\User\Email;
use App\Auth\Service\JoinConfirmationSender;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\MailerInterface;

final class JoinConfirmationSenderTest extends TestCase
{
    public function testSuccess(): void
    {
        $from = ['test@app.test' => 'Test'];
        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();
        $confirmUrl = '/join/confirm?token=' . $token;

        $symfonyEmail = new SymfonyEmail()
            ->to($to->getValue())
            ->subject('Join confirmation')
            ->html($confirmUrl);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')
            ->willReturnCallback(static function (SymfonyEmail $message) use ($symfonyEmail): int {
                self::assertEquals($symfonyEmail->getTo(), $message->getTo());
                self::assertEquals($symfonyEmail->getSubject(), $message->getSubject());
                self::assertStringContainsString($symfonyEmail->getHtmlBody(), $message->getHtmlBody());
                return 1;
            });

        $sender = new JoinConfirmationSender($mailer, $from);

        $sender->send($to, $token);
    }

    public function testError(): void
    {

        $to = new Email('user@app.test');
        $token = Uuid::uuid4()->toString();

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('Transport failed'));

        $sender = new JoinConfirmationSender($mailer);

        $this->expectException(TransportException::class);
        $sender->send($to, $token);
    }
}

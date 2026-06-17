<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Service;

use App\Auth\Entity\User\Email;
use App\Auth\Service\PasswordChangeSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class PasswordChangeSenderTest extends TestCase
{
    public function testSuccess(): void
    {
        $email = new Email('test@email.ru');
        $subject = 'Change password';
        $template = 'auth/password/change.html.twig';

        $loader = new ArrayLoader([
           $template => "<p>change password success</p>"
        ]);
        $mailer = $this->createMock(MailerInterface::class);
        $twig = new Environment($loader);

        $symfonyEmail = new SymfonyEmail()
            ->to($email->getValue())
            ->subject($subject)
            ->html($twig->render($template));

        $sender = new PasswordChangeSender($mailer, $twig);
        $mailer->expects(self::once())->method('send')->with(
            $this->equalTo($symfonyEmail)
        )->willReturnCallback(static function ($message) use ($symfonyEmail){
            /** @var SymfonyEmail $message */
            self::assertEquals($symfonyEmail->getSubject(), $message->getSubject());
            self::assertEquals($symfonyEmail->getHtmlBody(), $message->getHtmlBody());
            self::assertEquals($symfonyEmail->getTo(), $message->getTo());
        });

        $sender->send($email);
    }

    public function testError(): void
    {
        $email = new Email('test@email.ru');
        $subject = 'Change password';
        $template = 'auth/password/change.html.twig';

        $loader = new ArrayLoader([
            $template => "<p>change password success</p>"
        ]);
        $mailer = $this->createMock(MailerInterface::class);
        $twig = new Environment($loader);

        $symfonyEmail = new SymfonyEmail()
            ->to($email->getValue())
            ->subject($subject)
            ->html($twig->render($template));

        $sender = new PasswordChangeSender($mailer, $twig);
        $mailer->expects(self::once())->method('send')->with(
            $this->equalTo($symfonyEmail)
        )->willThrowException(new TransportException('TransportException'));

        $this->expectException(TransportException::class);
        $sender->send($email);
    }
}

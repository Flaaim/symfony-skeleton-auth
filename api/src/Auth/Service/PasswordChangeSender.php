<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Entity\User\Email;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Twig\Environment;

final class PasswordChangeSender
{
    public const string TEMPLATE = 'auth/password/change.html.twig';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function send(Email $email): void
    {
        $message = new SymfonyEmail()
            ->subject('Change password')
            ->to($email->getValue())
            ->html($this->twig->render(self::TEMPLATE));

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }
    }
}

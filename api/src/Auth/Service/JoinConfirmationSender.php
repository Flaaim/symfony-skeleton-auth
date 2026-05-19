<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Entity\User\Email;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


final class JoinConfirmationSender
{
    public function __construct(
        private MailerInterface $mailer
    ) {}
    public function send(Email $email, string $token): void
    {
        $message = new SymfonyEmail()
            ->subject('Join confirmation')
            ->to($email->getValue())
            ->html('/join/confirm?' . http_build_query([
                    'token' => $token,
                ]));
        try{
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Entity\User\Email;
use App\Auth\Event\PasswordChanged;
use App\Auth\Service\PasswordChangeSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendEmailOnPasswordChangedHandler
{

    public function __construct(
        private readonly PasswordChangeSender $sender,
        private readonly LoggerInterface $logger
    )
    {}
    public function __invoke(PasswordChanged $event): void
    {
        $email = $event->email;
        $userId = $event->userId;
        $this->sender->send(new Email($email));

        $this->logger->info(
            'Password user: '.$userId.' has been changed.' ,
        );
    }
}

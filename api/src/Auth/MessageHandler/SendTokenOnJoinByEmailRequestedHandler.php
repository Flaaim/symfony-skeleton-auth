<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Entity\User\Email;
use App\Auth\Event\JoinByEmailRequested;
use App\Auth\Service\JoinConfirmationSender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendTokenOnJoinByEmailRequestedHandler
{
    public function __construct(
        private readonly JoinConfirmationSender $sender
    ) {}

    public function __invoke(JoinByEmailRequested $event): void
    {
        $email = new Email($event->email);
        $token = $event->token;

        $this->sender->send(
            $email,
            $token,
        );
    }
}

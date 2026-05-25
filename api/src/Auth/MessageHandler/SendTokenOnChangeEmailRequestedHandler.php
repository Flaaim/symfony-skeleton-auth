<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Entity\User\Email;
use App\Auth\Event\ChangeEmailRequested;
use App\Auth\Service\NewEmailConfirmTokenSender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendTokenOnChangeEmailRequestedHandler
{
    public function __construct(
      private readonly NewEmailConfirmTokenSender $sender,
    ) {}

    public function __invoke(ChangeEmailRequested $event): void
    {
        $email = new Email($event->newEmail);
        $token = $event->newEmailToken;

        $this->sender->send($email, $token);
    }
}

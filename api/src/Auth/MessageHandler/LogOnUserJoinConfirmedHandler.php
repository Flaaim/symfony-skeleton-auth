<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\UserJoinConfirmed;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnUserJoinConfirmedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(UserJoinConfirmed $event): void
    {
        $email = $event->email;

        $this->logger->info('User confirmed ' . $email);
    }
}

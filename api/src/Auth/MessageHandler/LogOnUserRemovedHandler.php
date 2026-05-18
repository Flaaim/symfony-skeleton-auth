<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\UserRemoved;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnUserRemovedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(UserRemoved $event): void
    {
        $userId = $event->id;

        $this->logger->info('Removed user ' . $userId);
    }
}

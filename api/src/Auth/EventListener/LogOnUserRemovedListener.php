<?php

declare(strict_types=1);

namespace App\Auth\EventListener;

use App\Auth\Event\UserRemoved;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class LogOnUserRemovedListener
{

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}
    public function __invoke(UserRemoved $event): void
    {
        $userId = $event->id->getValue();

        $this->logger->info('Removed user ' . $userId);
    }
}

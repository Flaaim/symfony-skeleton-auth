<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\NetworkAttached;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnNetworkAttachedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(NetworkAttached $event): void
    {
        $network = $event->network;
        $identity = $event->identity;

        $this->logger->info('Network attached ', [
            'network' => $network,
            'identity' => $identity,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\UserRoleChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnUserRoleChangedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(UserRoleChanged $event): void
    {
        $id = $event->id;
        $role = $event->role;

        $this->logger->info(
            'User role changed',
            [
                'id' => $id,
                'role' => $role,
            ]
        );
    }
}

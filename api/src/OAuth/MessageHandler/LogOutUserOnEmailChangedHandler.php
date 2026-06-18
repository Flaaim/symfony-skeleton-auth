<?php

declare(strict_types=1);

namespace App\OAuth\MessageHandler;

use App\Auth\Event\EmailChanged;
use App\OAuth\Entity\RefreshTokenRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOutUserOnEmailChangedHandler
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshToken,
    ) {}

    public function __invoke(EmailChanged $event): void
    {
        $userId = $event->userId;

        $this->refreshToken->revokeForUser($userId);
    }
}

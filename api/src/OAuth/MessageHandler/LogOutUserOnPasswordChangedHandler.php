<?php

declare(strict_types=1);

namespace App\OAuth\MessageHandler;

use App\Auth\Event\PasswordChanged;
use App\OAuth\Entity\RefreshTokenRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOutUserOnPasswordChangedHandler
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshToken,
    ) {}

    public function __invoke(PasswordChanged $event): void
    {
        $this->refreshToken->revokeForUser($event->userId);
    }
}

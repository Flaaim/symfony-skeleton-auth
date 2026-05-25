<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        // TODO: Implement persistNewRefreshToken() method.
    }

    public function revokeRefreshToken(string $tokenId): void
    {
        // TODO: Implement revokeRefreshToken() method.
    }

    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        // TODO: Implement isRefreshTokenRevoked() method.
        return false;
    }
}

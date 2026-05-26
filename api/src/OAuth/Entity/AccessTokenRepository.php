<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        ?string $userIdentifier = null
    ): AccessTokenEntityInterface {
        $accessToken = new AccessToken($clientEntity, $scopes);

        if (null !== $userIdentifier) {
            $accessToken->setUserIdentifier($userIdentifier);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        // TODO: Implement persistNewAccessToken() method.
    }

    public function revokeAccessToken(string $tokenId): void
    {
        // TODO: Implement revokeAccessToken() method.
    }

    public function isAccessTokenRevoked(string $tokenId): bool
    {
        // TODO: Implement isAccessTokenRevoked() method.
    }
}

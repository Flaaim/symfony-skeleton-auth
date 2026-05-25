<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        // TODO: Implement persistNewAuthCode() method.
    }

    public function revokeAuthCode(string $codeId): void
    {
        // TODO: Implement revokeAuthCode() method.
    }

    public function isAuthCodeRevoked(string $codeId): bool
    {
        // TODO: Implement isAuthCodeRevoked() method.
        return false;
    }
}

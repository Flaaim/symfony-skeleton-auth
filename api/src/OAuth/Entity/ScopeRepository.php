<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

final class ScopeRepository implements ScopeRepositoryInterface
{
    /** @var array<Scope> */
    public function __construct(
        private array $scopes,
    ) {}

    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        return array_find($this->scopes, static fn ($scope) => $scope->getIdentifier() === $identifier);
    }

    public function finalizeScopes(array $scopes, string $grantType, ClientEntityInterface $clientEntity, ?string $userIdentifier = null, ?string $authCodeId = null): array
    {
        return $scopes;
    }
}

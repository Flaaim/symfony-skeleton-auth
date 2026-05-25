<?php

declare(strict_types=1);

namespace App\Infrastructure\OAuth;

use App\OAuth\Entity\Client;
use App\OAuth\Entity\ClientRepository;
use App\OAuth\Entity\Scope;
use App\OAuth\Entity\ScopeRepository;

final class OAuthRepositoryFactory
{
    /**
     * @param string[] $scopes
     */
    public static function createScopeRepository(array $scopes): ScopeRepository
    {
        $scopeObjects = array_map(static fn (string $item): Scope => new Scope($item), $scopes);

        return new ScopeRepository($scopeObjects);
    }

    /**
     * @param array<array-key, array{name: string, client_id: string, redirect_uri: string}> $clients
     */
    public static function createClientRepository(array $clients): ClientRepository
    {
        $clientObjects = array_map(static function (array $item): Client {
            return new Client(
                $item['client_id'],
                $item['name'],
                $item['redirect_uri']
            );
        }, $clients);

        return new ClientRepository($clientObjects);
    }
}

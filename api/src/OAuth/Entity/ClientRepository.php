<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{
    /** @var array<Client> $clients */
    public function __construct(
      private array $clients = [],
    ) {}
    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        return array_find($this->clients, fn($client) => $client->getIdentifier() === $clientIdentifier);

    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $client = $this->getClientEntity($clientIdentifier);

        if ($client === null) {
            return false;
        }

        if ($clientSecret !== null) {
            return false;
        }

        return true;
    }
}

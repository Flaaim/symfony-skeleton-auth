<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{
    /** @var array<Client> */
    public function __construct(
        private array $clients = [],
    ) {}

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        return array_find($this->clients, static fn ($client) => $client->getIdentifier() === $clientIdentifier);
    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $client = $this->getClientEntity($clientIdentifier);

        if (null === $client) {
            return false;
        }

        if (null !== $clientSecret) {
            return false;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Social\Registry;

use App\Infrastructure\Social\ClientInterface;

final class ClientRegistry
{
    /** @var array<ClientInterface> $clients */
    private array $clients;
    public function __construct(array $clients)
    {
        foreach ($clients as $client) {
            if(!$client instanceof ClientInterface){
                throw new \InvalidArgumentException('Clients must implement ClientInterface');
            }
        }
        $this->clients = $clients;
    }
    public function create(string $provider): ClientInterface
    {
        foreach ($this->clients as $client) {
            /** @var ClientInterface $client */
            if($client->getProvider() === $provider){
                return $client;
            }
        }
        throw new \DomainException('Provider {$provider} is not supported.');
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Social;

use App\Infrastructure\Social\Registry\Provider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GoogleClient implements ClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
    ) {}

    public function fetchUser(string $code): array
    {
        $tokenResponse = $this->client->request('POST', 'https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri
            ],
        ]);
        $tokenData = $tokenResponse->toArray();
        $googleAccessToken = $tokenData['access_token'] ?? null;
        if(!$googleAccessToken) {
            throw new \DomainException('Failed to get Google access token.');
        }
        $infoResponse = $this->client->request('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $googleAccessToken,
            ]
        ]);
        $userData = $infoResponse->toArray();
        return [
            'identity' => $userData['id'],
            'email' => $userData['email'] ?? null,
        ];
    }

    public function getProvider(): string
    {
        return Provider::Google->value;
    }
}

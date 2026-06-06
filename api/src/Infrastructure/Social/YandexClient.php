<?php

declare(strict_types=1);

namespace App\Infrastructure\Social;


use App\Infrastructure\Social\Registry\Provider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class YandexClient implements ClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    public function fetchUser(string $code): SocialUserDTO
    {
        $tokenResponse = $this->client->request('POST', 'https://oauth.yandex.ru/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);
        $tokenData = $tokenResponse->toArray();
        $yandexAccessToken = $tokenData['access_token'] ?? null;

        if (!$yandexAccessToken) {
            throw new \DomainException('Failed to get Yandex access token.');
        }
        $infoResponse = $this->client->request('GET', 'https://login.yandex.ru/info?format=json', [
            'headers' => [
                'Authorization' => 'OAuth ' . $yandexAccessToken,
            ],
        ]);

        $userData = $infoResponse->toArray();

        return new SocialUserDTO(
            (string)$userData['id'],
            $this->getProvider(),
            $userData['default_email'] ?? null
        );
    }
    public function getProvider(): string
    {
        return Provider::Yandex->value;
    }
}

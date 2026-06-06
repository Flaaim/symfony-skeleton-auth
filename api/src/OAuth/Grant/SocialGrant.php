<?php

declare(strict_types=1);

namespace App\OAuth\Grant;

use App\Auth\Command\JoinByNetwork\Command;
use App\Auth\Command\JoinByNetwork\Handler;
use App\Auth\Entity\User\Email;
use App\Infrastructure\Social\YandexClient;
use App\OAuth\Entity\UserAdapter;
use DateInterval;
use App\Auth\Entity\User\UserRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class SocialGrant extends AbstractGrant
{
    public function __construct(
        private readonly YandexClient $yandexClient,
        private readonly Handler $joinHandler,
        private readonly UserRepository $domainUserRepository,
        private readonly LoggerInterface $logger,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new \DateInterval('P1M');
    }
    public function getIdentifier(): string
    {
        return 'social';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface
    {
        $client = $this->validateClient($request);

        $parsedBody = $request->getParsedBody();
        $network = $parsedBody['network'] ?? null;
        $code = $parsedBody['code'] ?? null;

        if (!$network || !$code) {
            throw OAuthServerException::invalidRequest('network or code');
        }

        try{
            $socialUser = $this->yandexClient->fetchUser($code);
        }catch (\Throwable $e){
            $this->logger->error('Social Auth Error (Yandex fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка авторизации через соцсеть: ' . $e->getMessage());
        }

        try{
            $localUser = $this->domainUserRepository->findByEmail(new Email($socialUser['email']));
            if (!$localUser) {
                $command = new Command($socialUser['email'], $network, $socialUser['identity']);
                $this->joinHandler->handle($command);

                $localUser = $this->domainUserRepository->findByEmail(new Email($socialUser['email']));
            }
        }catch (\Throwable $e) {
            $this->logger->error('Social Auth Error (Yandex fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка при регистрации через соцсеть: ' . $e->getMessage());
        }

        if (!$localUser) {
            $this->logger->error('Social Auth Error (Yandex fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Не удалось получить пользователя после регистрации.');
        }

        try {
            $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

            $userIdentifier = (string)$localUser->getId()->getValue();


            $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes);

            $refreshToken = $this->issueRefreshToken($accessToken);

            $responseType->setAccessToken($accessToken);
            $responseType->setRefreshToken($refreshToken);

            return $responseType;
        } catch (\Throwable $e) {
            $this->logger->error('Social Auth Error (Yandex fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка на этапе генерации токенов: ' . $e->getMessage());
        }

    }
}

<?php

declare(strict_types=1);

namespace App\OAuth\Grant;

use App\Auth\Command\JoinByNetwork\Command;
use App\Auth\Command\JoinByNetwork\Handler;
use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Social\Registry\ClientRegistryInterface;
use DateInterval;
use DomainException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @psalm-suppress PropertyNotSetInConstructor Parent properties are set by OAuth server via setters.
 */
final class SocialGrant extends AbstractGrant
{
    public function __construct(
        private readonly ClientRegistryInterface $registry,
        private readonly Handler $joinHandler,
        private readonly UserRepository $domainUserRepository,
        private readonly LoggerInterface $logger,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    public function getIdentifier(): string
    {
        return 'social';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);

        $parsedBody = $request->getParsedBody();
        if (!\is_array($parsedBody)) {
            throw OAuthServerException::invalidRequest('network or code');
        }

        $network = $parsedBody['network'] ?? null;
        $code = $parsedBody['code'] ?? null;
        $redirectUri = $parsedBody['redirect_uri'] ?? null;

        if (!\is_string($network) || '' === $network
            || !\is_string($code) || '' === $code
            || !\is_string($redirectUri) || '' === $redirectUri) {
            throw OAuthServerException::invalidRequest('network or code');
        }

        try {
            $socialUser = $this->registry->create($code, $network, $redirectUri);
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Provider fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка авторизации через соцсеть: ' . $e->getMessage());
        }
        $this->logger->error('Social Auth Error (Provider fetch): {message}', [
            'message' => $socialUser->email,
        ]);
        try {
            $localUser = $this->domainUserRepository->findByNetwork($socialUser->network, $socialUser->identity);
            if (!$localUser) {
                if($socialUser->email === null) {
                    throw new \DomainException('Email cannot be null');
                }
                $existingEmailUser = $this->domainUserRepository->findByEmail(new Email($socialUser->email));
                if ($existingEmailUser) {
                    throw new DomainException('Пользователь с таким email уже существует. Войдите обычным способом и привяжите аккаунт в настройках профиля.');
                }

                $command = new Command($socialUser->email, $socialUser->network, $socialUser->identity);
                $this->joinHandler->handle($command);

                $localUser = $this->domainUserRepository->findByNetwork($socialUser->network, $socialUser->identity);
            }
        } catch (DomainException $e) {
            throw OAuthServerException::invalidGrant($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Database/Register):', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка при регистрации через соцсеть: ' . $e->getMessage());
        }

        if (!$localUser) {
            throw OAuthServerException::serverError('Не удалось получить пользователя после регистрации.');
        }

        try {
            $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

            $userIdentifier = $localUser->getId()->getValue();

            $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes);

            $refreshToken = $this->issueRefreshToken($accessToken);
            $responseType->setAccessToken($accessToken);
            if (null !== $refreshToken) {
                $responseType->setRefreshToken($refreshToken);
            }

            return $responseType;
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Token Generation):', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка на этапе генерации токенов: ' . $e->getMessage());
        }
    }
}

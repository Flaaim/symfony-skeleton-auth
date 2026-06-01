<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository as DomainUserRepository;
use App\Auth\Service\PasswordHasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

/** @psalm-suppress UnusedClass */
final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly DomainUserRepository $domainUserRepository,
        private readonly PasswordHasher $passwordHasher
    ) {}

    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $user = $this->domainUserRepository->findByEmail(new Email($username));

        if (null === $user) {
            return null;
        }

        if ($user->isWait()) {
            throw OAuthServerException::accessDenied('User is not confirmed.');
        }

        $hash = $user->getPasswordHash();
        if (null !== $hash) {
            if (!$this->passwordHasher->validate($password, $hash)) {
                return null;
            }
        }

        return new User($user->getId()->getValue());
    }
}

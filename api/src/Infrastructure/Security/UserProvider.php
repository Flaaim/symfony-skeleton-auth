<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository as DomainUserRepository;
use App\OAuth\Entity\UserAdapter;
use Exception;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly DomainUserRepository $users
    ) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return UserAdapter::class === $class || is_subclass_of($class, UserAdapter::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $user = $this->users->get(new Id($identifier));
        } catch (Exception $e) {
            throw new UserNotFoundException('User not found.');
        }
        if (!$user) {
            throw new UserNotFoundException('User not found.');
        }
        return new UserAdapter($user->getId()->getValue());
    }
}

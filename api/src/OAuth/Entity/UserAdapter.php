<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

final class UserAdapter implements UserInterface
{
    public function __construct(
        private readonly string $identifier,
    ) {}

    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_ADMIN'];
    }

    public function getUserIdentifier(): string
    {
        /** @var non-empty-string */
        return $this->identifier;
    }
}

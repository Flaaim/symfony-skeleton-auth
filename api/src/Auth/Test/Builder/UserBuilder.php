<?php

declare(strict_types=1);

namespace App\Auth\Test\Builder;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Role;
use App\Auth\Entity\User\Token;
use App\Auth\Entity\User\User;
use App\Auth\Service\PasswordHasher;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class UserBuilder
{
    private Id $id;
    private Email $email;
    private string $password;
    private DateTimeImmutable $date;
    private Token $joinConfirmToken;
    private ?Role $role = null;
    private bool $active = false;
    private ?string $network = null;
    private ?string $identity = null;
    private ?Token $newEmailChangeToken = null;
    private ?Email $newEmail = null;
    private PasswordHasher $hasher;

    public function __construct()
    {
        $this->id = Id::generate();
        $this->email = new Email('mail@example.com');
        $this->password = '123456';
        $this->date = new DateTimeImmutable();
        $this->joinConfirmToken = new Token(Uuid::uuid4()->toString(), $this->date->modify('+1 day'));
        $this->hasher = new PasswordHasher(16);
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withPassword(string $password): self
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }

    public function withRole(Role $role): self
    {
        $clone = clone $this;
        $clone->role = $role;
        return $clone;
    }

    public function withJoinConfirmToken(Token $token): self
    {
        $clone = clone $this;
        $clone->joinConfirmToken = $token;
        return $clone;
    }

    public function withNewEmailChangeToken(Token $token, Email $newEmail): self
    {
        $clone = clone $this;
        $clone->newEmailChangeToken = $token;
        $clone->newEmail = $newEmail;
        return $clone;
    }

    public function viaNetwork(string $network, string $identity): self
    {
        $clone = clone $this;
        $clone->network = $network;
        $clone->identity = $identity;
        return $clone;
    }

    public function withEmail(Email $email): self
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function active(): self
    {
        $clone = clone $this;
        $clone->active = true;
        return $clone;
    }

    public function build(): User
    {
        $user = User::requestJoinByEmail(
            $this->id,
            $this->date,
            $this->email,
            $this->hasher->hash($this->password),
            $this->joinConfirmToken
        );

        if ($this->active) {
            $user->confirmJoin(
                $this->joinConfirmToken->getValue(),
                $this->joinConfirmToken->getExpiresAt()->modify('-1 day')
            );
        }

        if (null !== $this->role) {
            $user->changeRole($this->role);
        }

        if (null !== $this->network && null !== $this->identity) {
            $user = User::joinByNetwork(
                $this->id,
                $this->date,
                $this->email,
                $this->network,
                $this->identity
            );
        }

        if (null !== $this->newEmailChangeToken && null !== $this->newEmail) {
            $user->requestEmailChanging(
                $this->newEmailChangeToken,
                $this->newEmailChangeToken->getExpiresAt()->modify('+1 day'),
                $this->newEmail
            );
        }

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use App\Auth\Service\PasswordHasher;
use ArrayObject;
use DateTimeImmutable;
use DomainException;

final class User
{
    private ?string $passwordHash = null;
    private ?Token $joinConfirmToken = null;
    private ArrayObject $networks;
    private ?Token $passwordResetToken = null;

    private function __construct(
        private Id $id,
        private DateTimeImmutable $date,
        private Email $email,
        private Status $status,
    ) {
        $this->networks = new ArrayObject();
    }

    public static function requestJoinByEmail(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        string $passwordHash,
        Token $token
    ): self {
        $user = new self($id, $date, $email, Status::wait());
        $user->passwordHash = $passwordHash;
        $user->joinConfirmToken = $token;
        return $user;
    }

    public static function joinByNetwork(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        NetworkIdentity $identity
    ) {
        $user = new self($id, $date, $email, Status::active());
        $user->networks->append($identity);
        return $user;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getJoinConfirmToken(): ?Token
    {
        return $this->joinConfirmToken;
    }

    public function attachNetwork(NetworkIdentity $identity): void
    {
        /** @var NetworkIdentity $existing */
        foreach ($this->networks as $existing) {
            if ($existing->isEqualTo($identity)) {
                throw new DomainException('Network is already attached.');
            }
        }
        $this->networks->append($identity);
    }

    public function requestPasswordReset(Token $token, DateTimeImmutable $date): void
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }
        if (null !== $this->passwordResetToken && !$this->passwordResetToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already requested.');
        }
        $this->passwordResetToken = $token;
    }

    public function getPasswordResetToken(): ?Token
    {
        return $this->passwordResetToken;
    }

    public function resetPassword(string $token, DateTimeImmutable $date, string $hash): void
    {
        if (null === $this->passwordResetToken) {
            throw new DomainException('Resetting is not requested.');
        }
        $this->passwordResetToken->validate($token, $date);
        $this->passwordResetToken = null;
        $this->passwordHash = $hash;
    }

    public function changePassword(string $current, string $new, PasswordHasher $hasher): void
    {
        if (null === $this->passwordHash) {
            throw new DomainException('User does not have an old password.');
        }
        if (!$hasher->validate($current, $this->passwordHash)) {
            throw new DomainException('Incorrect current password.');
        }
        $this->passwordHash = $hasher->hash($new);
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function confirmJoin(string $token, DateTimeImmutable $date): void
    {
        if (null === $this->joinConfirmToken) {
            throw new DomainException('Confirmation is not required.');
        }
        $this->joinConfirmToken->validate($token, $date);
        $this->status = Status::active();
        $this->joinConfirmToken = null;
    }

    /**
     * @return NetworkIdentity[]
     */
    public function getNetworks(): array
    {
        /** @var NetworkIdentity[] */
        return $this->networks->getArrayCopy();
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use DomainException;

interface UserRepository
{
    public function hasByEmail(Email $email): bool;

    public function findByJoinConfirmToken(string $token): ?User;

    public function findByPasswordResetToken(string $token): ?User;

    public function hasByNetwork(Network $identity): bool;

    public function findByNewEmailToken(string $token): ?User;

    /**
     * @throws DomainException
     */
    public function get(Id $id): User;

    /**
     * @throws DomainException
     */
    public function getByEmail(Email $email): User;

    public function add(User $user): void;

    public function remove(User $user): void;
}

<?php

declare(strict_types=1);

namespace App\Auth\Command\JoinByNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\NetworkIdentity;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use DateTimeImmutable;
use DomainException;
use Infrastructure\Doctrine\Flusher;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        $identity = new NetworkIdentity($command->network, $command->identity);
        $email = new Email($command->email);

        if ($this->users->hasByNetwork($identity)) {
            throw new DomainException('User with this network already exists.');
        }

        if ($this->users->hasByEmail($email)) {
            throw new DomainException('User with this email already exists.');
        }

        $user = User::joinByNetwork(
            Id::generate(),
            new DateTimeImmutable(),
            $email,
            $identity
        );

        $this->users->add($user);

        $this->flusher->flush();
    }
}

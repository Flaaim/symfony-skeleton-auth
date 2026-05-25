<?php

declare(strict_types=1);

namespace App\Auth\Command\JoinByNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Doctrine\Flusher;
use DateTimeImmutable;
use DomainException;

final class Handler
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        $email = new Email($command->email);
        if ($this->users->hasByNetwork($command->network, $command->identity)) {
            throw new DomainException('User with this network already exists.');
        }

        if ($this->users->hasByEmail($email)) {
            $user = $this->users->getByEmail(new Email($command->email));

            $user->attachNetwork($command->network, $command->identity);
            $this->flusher->flush();
            return;
        }

        $user = User::joinByNetwork(
            Id::generate(),
            new DateTimeImmutable(),
            $email,
            $command->network,
            $command->identity
        );

        $this->users->add($user);

        $this->flusher->flush();
    }
}

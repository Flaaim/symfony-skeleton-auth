<?php

declare(strict_types=1);

namespace App\Auth\Command\AttachNetwork;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Doctrine\Flusher;
use DomainException;

final class Handler
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        if ($this->users->hasByNetwork($command->network, $command->identity)) {
            throw new DomainException('User with this network already exists.');
        }

        $user = $this->users->get(new Id($command->userId));

        $user->attachNetwork($command->network, $command->identity);

        $this->flusher->flush();
    }
}

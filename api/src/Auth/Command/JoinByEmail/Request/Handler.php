<?php

declare(strict_types=1);

namespace App\Auth\Command\JoinByEmail\Request;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Service\JoinConfirmationSender;
use App\Auth\Service\PasswordHasher;
use App\Auth\Service\Tokenizer;
use DateTimeImmutable;
use DomainException;
use Infrastructure\Doctrine\Flusher;

final class Handler
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private Tokenizer $tokenizer,
        private Flusher $flusher,
        private JoinConfirmationSender $sender
    ) {}

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if ($this->users->hasByEmail($email)) {
            throw new DomainException('User already exists.');
        }
        $date = new DateTimeImmutable();

        $user = new User(
            Id::generate(),
            $date,
            $email,
            $this->hasher->hash($command->password),
            $token = $this->tokenizer->generate($date)
        );
    }
}

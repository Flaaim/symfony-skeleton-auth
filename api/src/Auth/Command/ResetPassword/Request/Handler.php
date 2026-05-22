<?php

declare(strict_types=1);

namespace App\Auth\Command\ResetPassword\Request;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Token;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Service\Tokenizer;
use App\Infrastructure\Doctrine\Flusher;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Handler
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Tokenizer $tokenizer,
        private readonly Flusher $flusher
    ) {}

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        $user = $this->users->getByEmail($email);

        $date = new DateTimeImmutable();

        $user->requestPasswordReset(
            $this->tokenizer->generate($date),
            $date
        );

        $this->flusher->flush();
    }
}

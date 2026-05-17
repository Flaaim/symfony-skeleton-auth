<?php

declare(strict_types=1);

namespace App\Auth\Command\AttachNetwork;

final class Command
{
    public function __construct(
        public string $email,
        public string $network,
        public string $identity
    ) {}
}

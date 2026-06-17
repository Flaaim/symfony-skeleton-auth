<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class PasswordChanged
{
    public function __construct(
        public string $userId,
        public string $email
    ) {}
}

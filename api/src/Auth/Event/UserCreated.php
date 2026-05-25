<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class UserCreated
{
    public function __construct(
        public string $email,
    ) {}
}

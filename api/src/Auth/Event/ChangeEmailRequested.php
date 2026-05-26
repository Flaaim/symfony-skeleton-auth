<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class ChangeEmailRequested
{
    public function __construct(
        public string $newEmail,
        public string $newEmailToken,
    ) {}
}

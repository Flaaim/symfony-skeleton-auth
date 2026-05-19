<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class UserRoleChanged
{
    public function __construct(
        public string $id,
        public string $role,
    ) {}
}

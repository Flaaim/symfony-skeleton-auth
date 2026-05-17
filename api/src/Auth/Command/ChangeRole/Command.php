<?php

declare(strict_types=1);

namespace App\Auth\Command\ChangeRole;

final class Command
{
    public function __construct(
        public string $id,
        public string $role,
    ) {}
}

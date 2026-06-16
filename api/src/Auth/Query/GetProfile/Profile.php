<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

final class Profile
{
    public function __construct(
        public string $id,
        public string $email,
        public array $networks = []
    ) {}
}

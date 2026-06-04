<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

final class Query
{
    public function __construct(
        public string $userId
    ) {}
}

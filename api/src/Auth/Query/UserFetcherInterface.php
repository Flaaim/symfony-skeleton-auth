<?php

declare(strict_types=1);

namespace App\Auth\Query;

interface UserFetcherInterface
{
    public function findProfile(string $userId): ?array;
}

<?php

declare(strict_types=1);

namespace App\Auth\ReadModel;

interface UserFetcherInterface
{
    public function findDetail(string $id): ?array;
}

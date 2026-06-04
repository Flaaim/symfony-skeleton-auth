<?php

namespace App\Auth\ReadModel;

interface UserFetcherInterface
{
    public function findDetail(string $id): ?array;
}

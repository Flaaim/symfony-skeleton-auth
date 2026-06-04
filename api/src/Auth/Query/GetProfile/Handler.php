<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

use App\Auth\ReadModel\UserFetcherInterface;
use DomainException;

final class Handler
{
    public function __construct(
        private readonly UserFetcherInterface $fetcher,
    ) {}

    public function handle(Query $query): array
    {
        $user = $this->fetcher->findDetail($query->userId);
        if (null === $user) {
            throw new DomainException('User not found');
        }
        return $user;
    }
}

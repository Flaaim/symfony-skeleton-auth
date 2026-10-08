<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

use App\Auth\Query\UserFetcherInterface;

final readonly class QueryHandler
{
    public function __construct(
        private UserFetcherInterface $users
    ) {}

    public function handle(Query $query): ProfileDTO
    {
        $profile = $this->users->findProfile($query->userId);

        if($profile === null) {
            throw new \DomainException('User not found.');
        }

        return ProfileDTO::fromArray($profile);
    }
}

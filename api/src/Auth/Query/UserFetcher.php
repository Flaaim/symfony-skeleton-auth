<?php

declare(strict_types=1);

namespace App\Auth\Query;

use App\Auth\ReadModel\UserFetcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;


final class UserFetcher implements UserFetcherInterface
{
    public function __construct(
        private readonly Connection $connection
    )
    {}

    /**
     * @throws Exception
     */
    public function findDetail(string $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id, email')
            ->from('users')
            ->where("id = :id")
            ->setParameter('id', $id)
            ->executeQuery();

        $result = $qb->fetchAssociative();

        return $result ?: null;
    }
}

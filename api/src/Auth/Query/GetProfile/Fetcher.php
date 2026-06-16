<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

use Doctrine\DBAL\Connection;
use DomainException;

final class Fetcher
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    public function fetch(Query $query): ?Profile
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('u.id, u.email', 'un.network', 'un.identity')
            ->from('users', 'u')
            ->leftJoin('u', 'user_networks', 'un', 'u.id = un.user_id')
            ->where('u.id = :id')
            ->setParameter('id', $query->userId)
            ->executeQuery();

        $result = $qb->fetchAllAssociative();
        if (empty($result)) {
            return null;
        }

        $profile = new Profile(
            $result[0]['id'],
            $result[0]['email'],
        );

        foreach ($result as $row) {
            if($row['network'] !== null) {
                $profile->networks[] = [
                    'network' => $row['network'],
                    'identity' => $row['identity']
                ];
            }
        }
        return $profile;
    }
}

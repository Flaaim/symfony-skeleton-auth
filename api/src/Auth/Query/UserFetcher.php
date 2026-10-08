<?php

declare(strict_types=1);

namespace App\Auth\Query;


use Doctrine\DBAL\Connection;

final readonly class UserFetcher implements UserFetcherInterface
{

    public function __construct(
        private Connection $connection,
    ) {}

    public function findProfile(string $userId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('u.id, u.email', 'un.network', 'un.identity')
            ->from('users', 'u')
            ->leftJoin('u', 'user_networks', 'un', 'u.id = un.user_id')
            ->where('u.id = :id')
            ->setParameter('id', $userId)
            ->executeQuery();

        $result = $qb->fetchAllAssociative();

        if (empty($result)) {
            return null;
        }

        $profile = [];
        $profile['id'] = $result[0]['id'];
        $profile['email'] = $result[0]['email'];

        foreach ($result as $row) {
            if (null !== $row['network']) {
                $profile['network'][] = [
                    'name' => $row['network'],
                    'identity' => $row['identity'],
                ];
            }
        }
        return $profile;
    }
}

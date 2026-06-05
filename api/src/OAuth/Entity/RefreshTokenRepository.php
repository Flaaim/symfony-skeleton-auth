<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

/** @psalm-suppress UnusedClass  */
final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private EntityRepository $repo;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        $this->repo = $this->em->getRepository(RefreshToken::class);
    }

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        if ($this->exists($refreshTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
        $this->em->persist($refreshTokenEntity);
        $this->em->flush();
    }

    public function revokeRefreshToken(string $tokenId): void
    {
        $token = $this->repo->find($tokenId);
        if (null !== $token) {
            /** @var RefreshToken $token */
            $token->revoked();
            $this->em->flush();
        }
    }

    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        return !$this->exists($tokenId);
    }
    public function revokeForUser(string $identifier): void
    {
        $this->repo->createQueryBuilder('t')
            ->update()
            ->set('t.revoked', ':revokedStatus')
            ->where('t.userIdentifier = :identifier')
            ->setParameter('revokedStatus', true)
            ->setParameter('identifier', $identifier)
            ->getQuery()->execute();
    }
    private function exists(string $id): bool
    {
        return $this->repo->createQueryBuilder('t')
            ->select('COUNT(t.identifier)')
            ->andWhere('t.identifier = :identifier')
            ->setParameter(':identifier', $id)
            ->getQuery()->getSingleScalarResult() > 0;
    }
}

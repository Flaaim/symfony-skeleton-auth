<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'user_networks')]
#[ORM\UniqueConstraint(name: 'network_identity_idx', columns: ['network', 'identity'])]
final class Network
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'networks')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Column(type: 'string', length: 16)]
        private string $network,
        #[ORM\Column(type: 'string', length: 16)]
        private string $identity
    ) {
        Assert::notEmpty($network);
        Assert::notEmpty($identity);

        $this->id = Uuid::uuid4()->toString();
    }

    public function isEqualTo(self $network): bool
    {
        return
            $this->getNetwork() === $network->getNetwork() &&
            $this->getIdentity() === $network->getIdentity();
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }
}

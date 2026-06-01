<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * @psalm-suppress MissingConstructor
 * @psalm-suppress ClassMustBeFinal
 */
#[ORM\Entity]
#[ORM\Table(name: 'oauth_refresh_tokens')]
class RefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait;
    use RefreshTokenTrait;

    /**
     * @var non-empty-string
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 80)]
    protected string $identifier;

    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $expiryDateTime;
    /**
     * @var non-empty-string|null
     */
    #[ORM\Column(type: 'guid', nullable: false)]
    private ?string $userIdentifier = null;

    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
        $this->userIdentifier = (string)$accessToken->getUserIdentifier();
    }

    public function getUserIdentifier(): ?string
    {
        /** @var non-empty-string|null */
        return $this->userIdentifier;
    }
}

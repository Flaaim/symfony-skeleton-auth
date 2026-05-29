<?php

declare(strict_types=1);

namespace App\Oauth\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

#[ORM\Entity]
#[ORM\Table(name: 'oauth_auth_codes')]
class AuthCode implements AuthCodeEntityInterface
{
    use AuthCodeTrait;
    use EntityTrait;
    use TokenEntityTrait;

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
    #[ORM\Column(type: 'guid')]
    protected ?string $userIdentifier = null;
}

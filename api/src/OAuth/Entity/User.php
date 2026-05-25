<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Webmozart\Assert\Assert;

final class User implements UserEntityInterface
{
    public function __construct(
        private readonly string $identifier
    ) {
        Assert::uuid($identifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

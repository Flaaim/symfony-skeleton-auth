<?php

declare(strict_types=1);

namespace App\Infrastructure\OAuth;

use App\OAuth\Grant\SocialGrant;
use League\Bundle\OAuth2ServerBundle\AuthorizationServer\GrantConfigurator;
use League\OAuth2\Server\AuthorizationServer;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: GrantConfigurator::class)]
final class SocialGrantConfiguratorDecorator
{
    public function __construct(
        private readonly GrantConfigurator $inner,
        private readonly SocialGrant $socialGrant
    ) {}

    public function __invoke(AuthorizationServer $authorizationServer): void
    {
        ($this->inner)($authorizationServer);
        $authorizationServer->enableGrantType($this->socialGrant, new \DateInterval('PT1H'));
    }
}

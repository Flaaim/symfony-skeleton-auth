<?php

declare(strict_types=1);

namespace App\Infrastructure\Social;

interface ClientInterface
{
    public function fetchUser(string $code, string $redirectUri): SocialUserDTO;

    public function getProvider(): string;
}

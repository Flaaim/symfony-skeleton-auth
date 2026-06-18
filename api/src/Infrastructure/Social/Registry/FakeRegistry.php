<?php

declare(strict_types=1);

namespace App\Infrastructure\Social\Registry;

use App\Infrastructure\Social\SocialUserDTO;

final class FakeRegistry implements ClientRegistryInterface
{
    public function create(string $code, string $provider, string $redirectUri): SocialUserDTO
    {
        return new SocialUserDTO($code, $provider, 'test@gmail.com');
    }
}

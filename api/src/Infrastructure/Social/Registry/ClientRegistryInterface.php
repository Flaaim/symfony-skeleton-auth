<?php

namespace App\Infrastructure\Social\Registry;

use App\Infrastructure\Social\SocialUserDTO;

interface ClientRegistryInterface
{
    public function create(string $code, string $provider, string $redirectUri): SocialUserDTO;
}

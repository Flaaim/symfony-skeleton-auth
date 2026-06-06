<?php

namespace App\Infrastructure\Social;

interface ClientInterface
{
    public function fetchUser(string $code): SocialUserDTO;
    public function getProvider(): string;
}

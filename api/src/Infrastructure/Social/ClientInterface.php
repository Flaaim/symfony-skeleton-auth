<?php

namespace App\Infrastructure\Social;

interface ClientInterface
{
    public function fetchUser(string $code): array;
    public function getProvider(): string;
}

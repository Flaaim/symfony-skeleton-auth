<?php

namespace App\Frontend;

interface FrontendUrlGeneratorInterface
{
    public function generate(string $uri, array $params = []): string;
}

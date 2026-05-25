<?php

declare(strict_types=1);

namespace App\Frontend;

interface FrontendUrlGeneratorInterface
{
    public function generate(string $uri, array $params = []): string;
}

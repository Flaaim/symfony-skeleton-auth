<?php

declare(strict_types=1);

namespace App\Frontend;

final class FrontendUrlGenerator implements FrontendUrlGeneratorInterface
{
    public function __construct(
        private readonly string $baseUrl
    ) {}

    public function generate(string $uri, array $params = []): string
    {
        return $this->baseUrl
            . ($uri ? '/' . $uri : '')
            . ($params ? '?' . http_build_query($params) : '');
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class NetworkAttached
{
    public function __construct(
        public readonly string $network,
        public readonly string $identity,
    ) {}
}

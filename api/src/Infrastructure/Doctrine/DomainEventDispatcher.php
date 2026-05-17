<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Psr\EventDispatcher\EventDispatcherInterface;

interface DomainEventDispatcher
{
    public function onFlush(): void;

    public function postFlush(): void;
}



<?php

declare(strict_types=1);

namespace Infrastructure\Doctrine;

interface Flusher
{
    public function flush(): void;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

final class Flusher
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}
    public function flush(): void
    {
        $this->em->flush();
    }
}

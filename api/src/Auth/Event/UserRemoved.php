<?php

declare(strict_types=1);

namespace App\Auth\Event;

use App\Auth\Entity\User\Id;

final class UserRemoved
{
    public function __construct(
        public Id $id
    ) {}

}

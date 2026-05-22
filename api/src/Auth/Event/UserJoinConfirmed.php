<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class UserJoinConfirmed
{
    public function __construct(
       public string $email
    ) {}
}

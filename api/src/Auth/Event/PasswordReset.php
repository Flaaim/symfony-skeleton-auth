<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class PasswordReset
{
    public function __construct(
       public readonly string $id
    ) {}
}

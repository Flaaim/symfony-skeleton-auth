<?php

declare(strict_types=1);

namespace App\Auth\Command\ResetPassword\Reset;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $token,
        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 32)]
        public readonly string $password
    ) {}
}

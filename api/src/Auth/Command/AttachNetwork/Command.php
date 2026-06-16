<?php

declare(strict_types=1);

namespace App\Auth\Command\AttachNetwork;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        public string $userId,
        #[Assert\NotBlank]
        public string $network,
        #[Assert\NotBlank]
        public string $identity
    ) {}
}

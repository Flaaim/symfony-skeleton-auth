<?php

declare(strict_types=1);

namespace App\Auth\Command\AttachNetwork;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        public string $network,
        #[Assert\NotBlank]
        public string $identity
    ) {}
}

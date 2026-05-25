<?php

declare(strict_types=1);

namespace App\Auth\Command\ChangePassword;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $id,
        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 32)]
        public string $current,
        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 32)]
        public string $new
    ) {}
}

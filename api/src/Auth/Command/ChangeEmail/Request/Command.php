<?php

declare(strict_types=1);

namespace App\Auth\Command\ChangeEmail\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $id,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
    ) {}
}

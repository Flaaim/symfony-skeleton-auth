<?php

declare(strict_types=1);

namespace App\Auth\Command\ChangeRole;

use App\Auth\Entity\User\Role;
use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $id,
        #[Assert\NotBlank]
        #[Assert\Choice(
            choices: [Role::ADMIN, Role::USER],
            message: 'The role must be a valid role.',
        )]
        public string $role,
    ) {}
}

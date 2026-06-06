<?php

declare(strict_types=1);

namespace App\Infrastructure\Social;

final class SocialUserDTO
{
    public function __construct(
       public string $identity,
       public string $network,
       public ?string $email,
    ) {
        $this->email = $email ?? sprintf('%s@%s.local', $identity, $network);
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

final class ProfileDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public array $networks = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            networks: $data['networks']
        );
    }
}

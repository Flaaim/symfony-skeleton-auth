<?php

declare(strict_types=1);

namespace App\Auth\Service;

use RuntimeException;
use Webmozart\Assert\Assert;

final class PasswordHasher
{
    public function hash(string $password): string
    {
        Assert::notEmpty($password);
        /** @var false|string|null $hash */
        $hash = password_hash($password, PASSWORD_ARGON2I);
        if (null === $hash) {
            throw new RuntimeException('Invalid hash algorithm.');
        }
        if (false === $hash) {
            throw new RuntimeException('Unable to generate hash.');
        }
        return $hash;
    }

    public function validate(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

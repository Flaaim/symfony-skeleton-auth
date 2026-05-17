<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Webmozart\Assert\Assert;

final class Role
{
    public const USER = 'user';
    public const TEACHER = 'teacher';
    public const ADMIN = 'admin';

    public function __construct(
        private string $name
    ) {
        Assert::oneOf($name, [
            self::USER,
            self::ADMIN,
            self::TEACHER,
        ]);
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public static function teacher(): self
    {
        return new self(self::TEACHER);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isEqualTo(self $other): bool
    {
        return $this->name === $other->name;
    }
}

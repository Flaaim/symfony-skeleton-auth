<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Webmozart\Assert\Assert;

final class Status
{
    public const WAIT = 'wait';
    public const ACTIVE = 'active';

    public function __construct(
        private readonly string $name
    ) {
        Assert::oneOf($name, [self::WAIT, self::ACTIVE]);
    }

    public static function wait(): self
    {
        return new self(self::WAIT);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public function isWait(): bool
    {
        return self::WAIT === $this->name;
    }

    public function isActive(): bool
    {
        return self::ACTIVE === $this->name;
    }

    public function getValue(): string
    {
        return $this->name;
    }
}

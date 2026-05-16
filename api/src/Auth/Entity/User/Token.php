<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use DateTimeImmutable;
use DomainException;
use Webmozart\Assert\Assert;

final class Token
{
    public function __construct(
        private string $value,
        private DateTimeImmutable $expiresAt
    ) {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function validate(string $value, DateTimeImmutable $date): void
    {
        if (!$this->isEqualTo($value)) {
            throw new DomainException('Token is invalid.');
        }
        if ($this->isExpiredTo($date)) {
            throw new DomainException('Token is expired.');
        }
    }
    private function isEqualTo(string $value): bool
    {
        return $this->value === $value;
    }
    private function isExpiredTo(DateTimeImmutable $date): bool
    {
        return $this->expiresAt <= $date;
    }
}

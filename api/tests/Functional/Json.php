<?php

declare(strict_types=1);

namespace Tests\Functional;

final class Json
{
    public static function decode(string $data): array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}

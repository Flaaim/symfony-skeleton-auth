<?php

declare(strict_types=1);

namespace App\Infrastructure\Social\Registry;

enum Provider: string
{
    case Yandex = 'yandex';
    case Google = 'google';
}

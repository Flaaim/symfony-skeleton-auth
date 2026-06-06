<?php

namespace App\Infrastructure\Social\Registry;

enum Provider: string
{
    case Yandex = 'yandex';
    case Google = 'google';
}

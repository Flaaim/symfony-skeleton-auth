<?php

declare(strict_types=1);

namespace App\Infrastructure\Social\Registry;

use App\Infrastructure\Social\SocialUserDTO;
use Tests\Functional\OAuth\AuthorizeFixture;

final class FakeRegistry implements ClientRegistryInterface
{
    public function create(string $code, string $provider, string $redirectUri): SocialUserDTO
    {
        $email = ('conflict' === $code) ? AuthorizeFixture::ACTIVE_EMAIL : 'test@gmail.com';
        return new SocialUserDTO($code, $provider, $email);
    }
}

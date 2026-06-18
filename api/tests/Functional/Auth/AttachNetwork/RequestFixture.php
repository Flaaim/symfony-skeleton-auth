<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\AttachNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const array JOIN_BY_GOOGLE = [
        'email' => 'test@gmail.com',
        'identity' => '0001',
        'network' => 'google',
        'code' => 'fake_google_code',
        'redirect_uri' => 'http://localhost/callback',
    ];

    public const array JOIN_BY_YANDEX = [
        'email' => 'test@yandex.ru',
        'network' => 'yandex',
        'identity' => '0002',
        'redirect_uri' => 'http://localhost/callback',
    ];

    public function load(ObjectManager $manager): void
    {
        $userWithGoogle = new UserBuilder()
            ->withEmail(new Email(self::JOIN_BY_GOOGLE['email']))
            ->viaNetwork(self::JOIN_BY_GOOGLE['network'], self::JOIN_BY_GOOGLE['identity'])
            ->build();

        $manager->persist($userWithGoogle);

        $userByEmail = new UserBuilder()
            ->withEmail(new Email(self::JOIN_BY_YANDEX['email']))
            ->active()
            ->build();

        $manager->persist($userByEmail);

        $manager->flush();
    }
}

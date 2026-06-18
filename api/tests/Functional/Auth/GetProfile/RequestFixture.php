<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\GetProfile;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ID = '00000000-0000-0000-0000-000000000002';
    public const string EMAIL = 'test@email.ru';
    public const string PASSWORD = 'password';

    public function load(ObjectManager $manager): void
    {
        $user = new UserBuilder()
            ->withId(new Id(self::ID))
            ->withEmail(new Email(self::EMAIL))
            ->withPassword(self::PASSWORD)
            ->active()
            ->build();

        $manager->persist($user);

        $manager->flush();
    }
}

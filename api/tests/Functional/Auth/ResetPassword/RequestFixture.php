<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ResetPassword;

use App\Auth\Entity\User\Email;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ACTIVE_EMAIL = 'active@test.ru';
    public const string NOT_ACTIVE_EMAIL = 'not_active@test.ru';

    public function load(ObjectManager $manager): void
    {
        $activeUser = new UserBuilder()
            ->withEmail(new Email(self::ACTIVE_EMAIL))
            ->active()
            ->build();

        $manager->persist($activeUser);

        $notActiveUser = new UserBuilder()
            ->withEmail(new Email(self::NOT_ACTIVE_EMAIL))
            ->build();

        $manager->persist($notActiveUser);

        $manager->flush();
    }
}

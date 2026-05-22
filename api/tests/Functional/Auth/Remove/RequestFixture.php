<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Remove;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ID_ACTIVE_USER = '2a974384-bcf1-4c48-ab4b-59b241420436';
    public const string ID_WAITING_USER = 'd45bef55-df21-49dd-98d0-f044532a928f';
    public function load(ObjectManager $manager): void
    {
        $activeUser = new UserBuilder()
            ->withId(new Id(self::ID_ACTIVE_USER))
            ->withEmail(new Email('active@email.com'))
            ->active()
            ->build();

        $manager->persist($activeUser);

        $waitingUser = new UserBuilder()
            ->withId(new Id(self::ID_WAITING_USER))
            ->withEmail(new Email('waiting@email.com'))
            ->build();

        $manager->persist($waitingUser);
        $manager->flush();

    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const array VALID = [
        'email' => 'email@test.ru',
        'userId' => 'fa666db8-4f82-4daf-8bc7-ebe510a1e9ae',
    ];
    public const array EXISTS = [
        'email' => 'exists@mail.ru',
        'userId' => '931227c6-5387-464e-9d83-833bbd4f540f',
    ];
    public const array NOT_ACTIVE = [
        'email' => 'notActive@mail.ru',
        'userId' => 'ab04db44-0c0c-43ae-b61e-a8c385a7cae9',
    ];

    public function load(ObjectManager $manager): void
    {
        $user = new UserBuilder()
            ->withId(new Id(self::VALID['userId']))
            ->withEmail(new Email(self::VALID['email']))
            ->active()
            ->build();

        $manager->persist($user);

        $exists = new UserBuilder()
            ->withId(new Id(self::EXISTS['userId']))
            ->withEmail(new Email(self::EXISTS['email']))
            ->active()
            ->build();

        $manager->persist($exists);

        $notActive = new UserBuilder()
            ->withId(new Id(self::NOT_ACTIVE['userId']))
            ->withEmail(new Email(self::NOT_ACTIVE['email']))
            ->build();

        $manager->persist($notActive);

        $manager->flush();
    }
}

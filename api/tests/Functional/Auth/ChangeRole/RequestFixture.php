<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeRole;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Role;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ADMIN_ID = '1ceb83d9-5b93-4f16-9960-cca38a9e5494';
    public const string USER_ID = 'df7fc17b-0681-4e4e-93dc-c6b0dfff9fff';
    public const string USER_EMAIL = 'user@email.ru';

    public function load(ObjectManager $manager): void
    {
        $admin = new UserBuilder()
            ->withId(new Id(self::ADMIN_ID))
            ->withEmail(new Email('admin@email.ru'))
            ->withRole(new Role(Role::ADMIN))
            ->build();

        $manager->persist($admin);

        $user = new UserBuilder()
            ->withId(new Id(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->build();

        $manager->persist($user);

        $manager->flush();
    }
}

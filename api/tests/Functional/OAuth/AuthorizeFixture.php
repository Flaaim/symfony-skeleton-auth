<?php

declare(strict_types=1);

namespace Tests\Functional\OAuth;

use App\Auth\Entity\User\Email;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class AuthorizeFixture extends AbstractFixture
{
    public const string ACTIVE_EMAIL = 'active@app.test';
    public const string WAIT_EMAIL = 'wait@app.test';
    public const string PASSWORD = 'password';
    public function load(ObjectManager $manager): void
    {
        $user = new UserBuilder()
            ->withEmail(new Email(self::ACTIVE_EMAIL))
            ->withPassword(self::PASSWORD)
            ->active()
            ->build();
        $manager->persist($user);

        $user = new UserBuilder()
            ->withEmail(new Email(self::WAIT_EMAIL))
            ->withPassword(self::PASSWORD)
            ->build();
        $manager->persist($user);

        $manager->flush();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Join;

use App\Auth\Entity\User\Email;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{

    public function load(ObjectManager $manager): void
    {
        $user = new UserBuilder()
            ->withEmail(new Email('exists@email.com'))
            ->active()
            ->build();

        $manager->persist($user);
        $manager->flush();
    }
}

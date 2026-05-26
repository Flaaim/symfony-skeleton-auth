<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Token;
use App\Auth\Test\Builder\UserBuilder;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class ConfirmFixture extends AbstractFixture
{
    public const array REQUESTED = [
        'token' => '1abdfc3d-b94d-499d-a8a4-6e4042c53043',
        'email' => 'new_email@mail.ru',
    ];
    public const string VALID_TOKEN = '1abdfc3d-b94d-499d-a8a4-6e4042c53043';
    public const string NEW_EMAIL = 'new_email@mail.ru';

    public function load(ObjectManager $manager): void
    {
        $requested = new UserBuilder()
            ->active()
            ->withNewEmailChangeToken(
                new Token(self::VALID_TOKEN, new DateTimeImmutable('+ 1 day')),
                new Email(self::NEW_EMAIL)
            )
            ->build();

        $manager->persist($requested);

        $notRequested = new UserBuilder()
            ->active()
            ->build();

        $manager->persist($notRequested);

        $manager->flush();
    }
}

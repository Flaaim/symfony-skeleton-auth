<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ResetPassword;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Token;
use App\Auth\Service\Tokenizer;
use App\Auth\Test\Builder\UserBuilder;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class ResetFixture extends AbstractFixture
{
    public const string ACTIVE_TOKEN = 'c1118378-eb36-45b8-a829-7fda473e8782';
    public const string EXPIRED_TOKEN = 'a409075f-6d98-47cd-9ed0-72c80e7b2fc2';
    public const string EMAIL = 'user@test.ru';
    public function load(ObjectManager $manager): void
    {
        $userWithActiveRequest = new UserBuilder()
            ->withEmail(new Email(self::EMAIL))
            ->active()
            ->build();

        $date = new DateTimeImmutable();
        $token = $this->getToken($date, self::ACTIVE_TOKEN);

        $userWithActiveRequest->requestPasswordReset($token, $date);

        $manager->persist($userWithActiveRequest);
        $manager->flush();

        $userWithExpiredRequest = new UserBuilder()
            ->withEmail(new Email('test@email.com'))
            ->active()
            ->build();

        $date = new DateTimeImmutable('-1 day');
        $token = $this->getToken($date, self::EXPIRED_TOKEN);
        $userWithExpiredRequest->requestPasswordReset($token, $date);

        $manager->persist($userWithExpiredRequest);
        $manager->flush();
    }

    private function getToken(DateTimeImmutable $date, string $token): Token
    {
        return new Token(
            $token,
            $date->add(new \DateInterval('PT1H'))
        );
    }
}

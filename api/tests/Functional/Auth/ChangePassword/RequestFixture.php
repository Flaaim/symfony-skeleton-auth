<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Token;
use App\Auth\Entity\User\User;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

final class RequestFixture extends AbstractFixture
{
    public const string USER_ID = '6e95c8fa-a250-4bdb-9fa3-38edf14a2a8d';
    public const string EMAIL = 'email@test.ru';
    public const PASSWORD = 'password';
    public const array JOIN_BY_GOOGLE = [
        'userId' => 'd0d6e420-0e54-47c5-a90d-af75e8c8c7a6',
        'email' => 'test@gmail.com',
        'network' => 'google',
        'identity' => '00001',
    ];

    public function load(ObjectManager $manager): void
    {
        $user = User::requestJoinByEmail(
            new Id(self::USER_ID),
            $date = new DateTimeImmutable(),
            new Email(self::EMAIL),
            $this->hash(self::PASSWORD),
            new Token($value = Uuid::uuid4()->toString(), $date->modify('+1 day'))
        );

        $user->confirmJoin($value, $date);

        $manager->persist($user);

        $userWithGoogle = User::joinByNetwork(
            new Id(self::JOIN_BY_GOOGLE['userId']),
            new DateTimeImmutable(),
            new Email(self::JOIN_BY_GOOGLE['email']),
            self::JOIN_BY_GOOGLE['network'],
            self::JOIN_BY_GOOGLE['identity']
        );

        $manager->persist($userWithGoogle);

        $manager->flush();
    }

    private function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2I, ['memory_cost' => 16]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    private readonly KernelBrowser $client;

    private readonly UserRepository $users;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testNotFound(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'userId' => 'd2b1416a-cf3b-4212-a9cb-7f2264eeed71',
            'currentPassword' => 'hashedPassword',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not found.'], $data);
    }

    public function testOldPasswordNotFound(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'userId' => RequestFixture::JOIN_BY_GOOGLE['userId'],
            'currentPassword' => 'hashedPassword',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User does not have an old password.'], $data);
    }

    public function testIncorrectCurrentPassword(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'userId' => RequestFixture::USER_ID,
            'currentPassword' => 'Incorrect',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect current password.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change',[
            'userId' => RequestFixture::USER_ID,
            'currentPassword' => RequestFixture::PASSWORD,
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::USER_ID));

        self::assertTrue(password_verify('newPassword', $user->getPasswordHash()));
    }
}

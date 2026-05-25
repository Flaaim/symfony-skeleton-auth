<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
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
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => Uuid::uuid4()->toString(),
            'email' => 'email@email.com',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not found.'], $data);
    }

    public function testEmailExists(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => RequestFixture::EXISTS['email'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Email is already in use.'], $data);
    }

    public function testNotActive(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::NOT_ACTIVE['userId'],
            'email' => 'some@email.ru',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not active.'], $data);
    }

    public function testEmailTheSame(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => RequestFixture::VALID['email'],
        ]);
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Email is already same.'], $data);
    }

    public function testRequestAlready(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => 'some@email.ru',
        ]);

        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => 'another@email.ru',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Changing is already requested.'], $data);
        $user = $this->users->get(new Id(RequestFixture::VALID['userId']));

        self::assertNotNull($user->getNewEmailToken());
    }

    public function testSuccess(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/email/change-request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => 'some@email.ru',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::VALID['userId']));
        self::assertNotNull($user->getNewEmailToken());
    }
}

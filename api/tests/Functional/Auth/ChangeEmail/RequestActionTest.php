<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\ChangeEmailRequested;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
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
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
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
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
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
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request',[
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
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request',[
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
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => 'some@email.ru',
        ]);

        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
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
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'userId' => RequestFixture::VALID['userId'],
            'email' => 'some@email.ru',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::VALID['userId']));
        self::assertNotNull($user->getNewEmailToken());

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();
        self::assertInstanceOf(ChangeEmailRequested::class, $message);

        self::assertEquals($user->getNewEmail()->getValue(), $message->newEmail);
        self::assertEquals($user->getNewEmailToken()->getValue(), $message->newEmailToken);
    }
}

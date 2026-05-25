<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\AttachNetwork;

use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\NetworkAttached;
use Doctrine\ORM\EntityManagerInterface;
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
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testAlreadyAttached(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/attach', [
            'email' => RequestFixture::JOIN_BY_GOOGLE['email'],
            'network' => RequestFixture::JOIN_BY_GOOGLE['network'],
            'identity' => RequestFixture::JOIN_BY_GOOGLE['identity'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User with this network already exists.'], $data);
    }

    public function testUserNotFound(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/attach', [
            'email' => 'not-exists@test.ru',
            'network' => RequestFixture::JOIN_BY_GOOGLE['email'],
            'identity' => RequestFixture::JOIN_BY_GOOGLE['network'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not found.'], $data);
    }

    public function testSuccess(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();
        $this->client->catchExceptions(false);
        $this->client->jsonRequest('POST', '/v1/auth/join/network/attach', [
            'email' => RequestFixture::JOIN_BY_YANDEX['email'],
            'network' => RequestFixture::JOIN_BY_YANDEX['network'],
            'identity' => RequestFixture::JOIN_BY_YANDEX['identity'],
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertTrue($this->users->hasByNetwork(
            RequestFixture::JOIN_BY_YANDEX['network'],
            RequestFixture::JOIN_BY_YANDEX['identity']
        ));

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(NetworkAttached::class, $message);

        self::assertEquals(RequestFixture::JOIN_BY_YANDEX['network'], $message->network);
        self::assertEquals(RequestFixture::JOIN_BY_YANDEX['identity'], $message->identity);
    }

    public function testEmpty(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/attach');

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'email' => 'This value should not be blank.',
            'network' => 'This value should not be blank.',
            'identity' => 'This value should not be blank.',
        ]], $data);
    }

    public function testInvalidEmail(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/request', [
            'email' => 'invalid',
            'network' => 'facebook',
            'identity' => '0002',
        ]);
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'email' => 'This value is not a valid email address.',
        ]], $data);
    }
}

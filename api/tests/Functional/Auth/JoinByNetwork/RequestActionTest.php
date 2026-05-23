<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\JoinByNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\NetworkAttached;
use App\Auth\Event\UserCreated;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class RequestActionTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    private readonly UserRepository $users;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = RequestActionTest::createClient();
        $container = $this->client->getContainer();

        $fixture = new FixturesLoader($container);
        $fixture->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testAlreadyJoinedNetwork(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/request', [
            'email' => RequestFixture::JOIN_BY_GOOGLE['email'],
            'network' => RequestFixture::JOIN_BY_GOOGLE['network'],
            'identity' => RequestFixture::JOIN_BY_GOOGLE['identity'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'User with this network already exists.'], $data);

    }
    public function testAlreadyJoinedByEmail(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/join/network/request', [
            'email' => RequestFixture::EXISTS_EMAIL,
            'network' => 'facebook',
            'identity' => '0002',
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertTrue($this->users->hasByNetwork('facebook', '0002'));

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(NetworkAttached::class, $message);

        self::assertEquals('facebook', $message->network);
        self::assertEquals('0002', $message->identity);
    }

    public function testSuccessJoinByNetwork(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/join/network/request', [
            'email' => RequestFixture::JOIN_BY_YANDEX['email'],
            'network' => RequestFixture::JOIN_BY_YANDEX['network'],
            'identity' => RequestFixture::JOIN_BY_YANDEX['identity'],
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertTrue($this->users->hasByNetwork(
            RequestFixture::JOIN_BY_YANDEX['network'],
            RequestFixture::JOIN_BY_YANDEX['identity']
        ));
        $user = $this->users->getByEmail(new Email(RequestFixture::JOIN_BY_YANDEX['email']));
        self::assertTrue($user->isActive());

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();
        self::assertInstanceOf(UserCreated::class, $message);

        self::assertEquals(RequestFixture::JOIN_BY_YANDEX['email'], $message->email);

    }

    public function testEmpty(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join/network/request');

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
            'email' => 'This value is not a valid email address.'
        ]], $data);
    }

}

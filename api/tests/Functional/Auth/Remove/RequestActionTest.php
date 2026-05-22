<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Remove;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\UserRemoved;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class RequestActionTest extends WebTestCase
{
    private readonly  KernelBrowser $client;
    private readonly UserRepository $users;
    public function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);

    }
    public function testRemoveActive(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/user/remove', [
            'userId' => RequestFixture::ID_ACTIVE_USER
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Unable to remove active user.'], $data);
        self::assertTrue($this->users->hasByEmail(new Email('active@email.com')));
    }

    public function testRemoveWaiting(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/user/remove', [
            'userId' => RequestFixture::ID_WAITING_USER
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        self::assertFalse($this->users->hasByEmail(new Email('waiting@email.com')));

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();
        self::assertInstanceOf(UserRemoved::class, $message);

    }
}

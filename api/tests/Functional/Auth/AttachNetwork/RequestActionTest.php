<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\AttachNetwork;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\NetworkAttached;
use App\OAuth\Entity\UserAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;
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
    private readonly User $authenticatedUserByGoogle;
    private readonly User $authenticatedUserByEmail;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->disableReboot();


        $container = $this->client->getContainer();
        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $this->users = new UserRepository($em);

        $this->authenticatedUserByGoogle = $this->users->findByEmail(new Email(RequestFixture::JOIN_BY_GOOGLE['email']));
        $this->authenticatedUserByEmail = $this->users->findByEmail(new Email(RequestFixture::JOIN_BY_YANDEX['email']));
    }
    public function testAlreadyAttached(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUserByGoogle->getId()->getValue()));

        $this->client->jsonRequest('POST', '/v1/auth/network/attach', [
            'network' => RequestFixture::JOIN_BY_GOOGLE['network'],
            'code' => RequestFixture::JOIN_BY_GOOGLE['identity'],
            'redirect_uri' => RequestFixture::JOIN_BY_GOOGLE['redirect_uri'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User with this network already exists.'], $data);
    }

    public function testUserUnauthorized(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/network/attach', [
            'network' => RequestFixture::JOIN_BY_GOOGLE['network'],
            'code' => RequestFixture::JOIN_BY_GOOGLE['identity'],
            'redirect_uri' => RequestFixture::JOIN_BY_GOOGLE['redirect_uri'],
        ]);
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Unauthorized. Please provide a valid Bearer token.'], $data);

    }

    public function testSuccess(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUserByEmail->getId()->getValue()));
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/network/attach', [
            'network' => RequestFixture::JOIN_BY_YANDEX['network'],
            'code' => RequestFixture::JOIN_BY_YANDEX['identity'],
            'redirect_uri' => RequestFixture::JOIN_BY_YANDEX['redirect_uri'],
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
        $this->client->loginUser(new UserAdapter($this->authenticatedUserByEmail->getId()->getValue()));

        $this->client->jsonRequest('POST', '/v1/auth/network/attach');

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['error' => 'Network, code or redirect uri are required.'], $data);
    }

}

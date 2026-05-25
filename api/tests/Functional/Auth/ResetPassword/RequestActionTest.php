<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ResetPassword;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\PasswordResetRequested;
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
    private KernelBrowser $client;
    private UserRepository $users;

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

    public function testSuccess(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', [
            'email' => RequestFixture::ACTIVE_EMAIL,
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->getByEmail(new Email(RequestFixture::ACTIVE_EMAIL));

        self::assertNotNull($user->getPasswordResetToken());

        self::assertCount(1, $transport->getSent());
        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(PasswordResetRequested::class, $message);

        self::assertEquals($user->getEmail()->getValue(), $message->email);
        self::assertEquals($user->getPasswordResetToken()->getValue(), $message->token);
    }

    public function testUserNotActive(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', [
            'email' => RequestFixture::NOT_ACTIVE_EMAIL,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals([
            'message' => 'User is not active.',
        ], $data);
    }

    public function testResetAlready(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', [
            'email' => RequestFixture::ACTIVE_EMAIL,
        ]);

        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', [
            'email' => RequestFixture::ACTIVE_EMAIL,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Resetting is already requested.'], $data);
    }

    public function testInvalid(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', [
            'email' => 'invalid-email',
        ]);
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'email' => 'This value is not a valid email address.',
        ]], $data);
    }

    public function testEmpty(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/password/reset-request', []);
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals([
            'errors' => [
                'email' => 'This value should not be blank.',
            ],
        ], $data);
    }
}

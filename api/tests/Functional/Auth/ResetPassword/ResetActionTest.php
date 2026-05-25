<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ResetPassword;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\PasswordReset;
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
final class ResetActionTest extends WebTestCase
{
    private KernelBrowser $client;

    private UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([ResetFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testSuccess(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('PUT', '/v1/auth/password/reset', [
            'token' => ResetFixture::ACTIVE_TOKEN,
            'password' => 'new-password',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->getByEmail(new Email(ResetFixture::EMAIL));

        self::assertNull($user->getPasswordResetToken());

        self::assertCount(1, $transport->getSent());
        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(PasswordReset::class, $message);

        self::assertEquals($user->getId()->getValue(), $message->id);
    }

    public function testTokenNotFound(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/password/reset', [
            'token' => Uuid::uuid4()->toString(),
            'password' => 'new-password',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals([
            'message' => 'Token is not found.',
        ], $data);
    }

    public function testTokenInvalid(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/password/reset', [
            'token' => 'invalid-token',
            'password' => 'new-password',
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);
        self::assertEquals(['errors' => ['token' => 'This is not a valid UUID.']], $data);
    }

    public function testTokenExpired(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/password/reset', [
            'token' => ResetFixture::EXPIRED_TOKEN,
            'password' => 'new-password',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Token is expired.'], $data);
    }
}

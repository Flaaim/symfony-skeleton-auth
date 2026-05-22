<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Join;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\UserJoinConfirmed;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class ConfirmActionTest extends WebTestCase
{
    private  KernelBrowser $client;
    private UserRepository $users;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([ConfirmFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testSuccess(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();
        $this->client->jsonRequest('POST', '/v1/auth/confirm', [
            'token' => ConfirmFixture::VALID
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->getByEmail(new Email(ConfirmFixture::VALID_EMAIL));
        self::assertTrue($user->isActive());

        self::assertCount(1, $transport->getSent());
        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(UserJoinConfirmed::class, $message);
        self::assertEquals($user->getEmail()->getValue(), $message->email);
    }

    public function testExpired(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/confirm', [
            'token' => ConfirmFixture::EXPIRED
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Token is expired.'], $data);
    }

    public function testEmpty(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/confirm');

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);
        self::assertEquals(['errors' => [
            'token' => 'This value should not be blank.'
        ]], $data);
    }

    public function testNotExisting(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/confirm', [
           'token' => Uuid::uuid4()->toString()
        ]);
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect token.'], $data);
    }

    public function testInvalidToken(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/confirm', [
            'token' => 'invalid_token'
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'token' => 'This is not a valid UUID.'
        ]], $data);
    }
}

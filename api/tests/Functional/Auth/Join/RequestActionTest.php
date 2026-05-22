<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Join;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\JoinByEmailRequested;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class RequestActionTest extends WebTestCase
{
    private  KernelBrowser $client;
    private UserRepository $users;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }
    public function testUserAlreadyExists(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join', [
            'email' => 'exists@email.com',
            'password' => 'password',
        ]);


        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'User already exists.'], $data);
    }

    public function testSuccess(): void
    {
        $email = 'new@email.com';
        $password = 'password';
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/auth/join', [
            'email' => $email,
            'password' => $password,
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());
        $user = $this->users->getByEmail(new Email($email));

        self::assertEquals($user->getEmail()->getValue(), $email);

        self::assertCount(2, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(JoinByEmailRequested::class, $message);
        self::assertEquals($email, $message->email);
        self::assertNotEmpty($message->token);
    }

    public function testInvalidCredentials(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/join', [
            'email' => 'some_text',
            'password' => '',
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'email' => 'This value is not a valid email address.',
            'password' => 'This value is too short. It should have 6 characters or more.',
        ]], $data);
    }
}

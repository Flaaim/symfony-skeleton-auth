<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class ConfirmActionTest extends WebTestCase
{
    private readonly KernelBrowser $client;

    private readonly UserRepository $users;
    public function setUp(): void
    {
        $this->client = ConfirmActionTest::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([ConfirmFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testNotFound(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/confirm', [
            'token' => '3ba74adc-f54a-476f-af8e-02adf73c5c7a'
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect token.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->catchExceptions(false);
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/confirm', [
           'token' => ConfirmFixture::VALID_TOKEN
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->getByEmail(new Email(ConfirmFixture::NEW_EMAIL));
        self::assertNull($user->getNewEmailToken());
        self::assertNull($user->getNewEmail());

    }

}

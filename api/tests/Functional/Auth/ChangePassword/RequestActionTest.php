<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class RequestActionTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = RequestActionTest::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);
    }

    public function testNotFound(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/user/change-password', [
            'userId' => 'd2b1416a-cf3b-4212-a9cb-7f2264eeed71',
            'currentPassword' => 'hash',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not found.'], $data);
    }
    public function testOldPasswordNotFound(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/user/change-password', [
            'userId' => RequestFixture::JOIN_BY_GOOGLE['userId'],
            'currentPassword' => RequestFixture::JOIN_BY_GOOGLE['currentPassword'],
            'newPassword' => RequestFixture::JOIN_BY_GOOGLE['newPassword'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User does not have an old password.'], $data);
    }
    public function testIncorrectCurrentPassword(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/user/change-password', [
            'userId' => RequestFixture::USER_ID,
            'currentPassword' => 'Incorrect',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect current password.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->jsonRequest('POST', '/v1/auth/user/change-password', [
            'userId' => RequestFixture::USER_ID,
            'currentPassword' => RequestFixture::PASSWORD,
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());
    }
}

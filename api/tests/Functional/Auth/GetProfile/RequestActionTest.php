<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\GetProfile;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;
use Tests\Functional\OAuth\AuthorizeFixture;

final class RequestActionTest extends WebTestCase
{
    private KernelBrowser $client;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = RequestActionTest::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

    }

    public function testUnloggedUser(): void
    {
        $this->client->request('GET', '/v1/user/profile');

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals([
            'message' => 'Access Denied.',
        ], $data);
    }

    public function testSuccess(): void
    {
        $this->client->request(
            'POST',
            '/token',
            [
                'grant_type' => 'password',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'username' => RequestFixture::EMAIL,
                'password' => RequestFixture::PASSWORD,
            ]
        );

        self::assertJson($content = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($content);

        $this->client->jsonRequest('GET', '/v1/user/profile', [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['access_token'],
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($body);
        self::assertEquals([
            'id' => RequestFixture::ID,
            'email' => RequestFixture::EMAIL
        ], $data);
    }
}

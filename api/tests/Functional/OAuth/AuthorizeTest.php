<?php

declare(strict_types=1);

namespace Tests\Functional\OAuth;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\ArraySubsetAssertTrait;
use Tests\Functional\Auth\Join\RequestFixture;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class AuthorizeTest extends WebTestCase
{
    use ArraySubsetAssertTrait;
    private readonly KernelBrowser $client;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = AuthorizeTest::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([AuthorizeFixture::class]);

    }

    public function testWithoutParams(): void
    {
        $this->client->request('POST', '/token');
        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testInvalidClient(): void
    {
        $this->client->request('POST', '/token',[
            'grant_type' => 'password',
            'client_id' => 'invalid',
            'client_secret' => 'my-super-secret-123',
            'username' => AuthorizeFixture::ACTIVE_EMAIL,
            'password' => AuthorizeFixture::PASSWORD,
        ]);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($content);

        self::assertArraySubset([
            'error' => 'invalid_client',
        ], $data);
    }

    public function testAuthActiveUser(): void
    {
        $this->client->request('POST', '/token', [
                'grant_type' => 'password',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'username' => AuthorizeFixture::ACTIVE_EMAIL,
                'password' => AuthorizeFixture::PASSWORD,
            ]
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($content = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($content);

        self::assertArraySubset([
            'token_type' => 'Bearer',
            'expires_in' => 600,
        ], $data);
    }

    public function testAuthWaitUser(): void
    {
        $this->client->request('POST', '/token',
            [
                'grant_type' => 'password',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'username' => AuthorizeFixture::WAIT_EMAIL,
                'password' => AuthorizeFixture::PASSWORD,
            ]
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
        self::assertNotEmpty($content = (string)$this->client->getResponse()->getContent());
        self::assertStringContainsString('User is not confirmed.', $content);
    }

    public function testAuthInvalidUser(): void
    {
        $this->client->request('POST', '/token' , [
                'grant_type' => 'password',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'username' => AuthorizeFixture::ACTIVE_EMAIL,
                'password' => '',
            ]
        );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArraySubset([
            'error' => 'invalid_request',
        ], $data);
    }
}

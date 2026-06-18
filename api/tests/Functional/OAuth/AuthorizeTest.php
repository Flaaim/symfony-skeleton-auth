<?php

declare(strict_types=1);

namespace Tests\Functional\OAuth;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\ArraySubsetAssertTrait;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

/**
 * @internal
 * @coversNothing
 */
final class AuthorizeTest extends WebTestCase
{
    use ArraySubsetAssertTrait;
    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
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
        $this->client->request('POST', '/token', [
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
        $this->client->request(
            'POST',
            '/token',
            [
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
        $this->client->request(
            'POST',
            '/token',
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
        $this->client->request(
            'POST',
            '/token',
            [
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
    public function testSocialAuthSuccess(): void
    {
        $this->client->request(
            'POST',
            '/token',
            [
                'grant_type' => 'social',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'network' => 'google',
                'code' => 'new-code-identity',
                'redirect_uri' => 'http://localhost/callback',
            ]
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArraySubset([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], $data);

        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('refresh_token', $data);
    }
    public function testSocialAuthEmailConflict(): void
    {
        $this->client->request(
            'POST',
            '/token',
            [
                'grant_type' => 'social',
                'client_id' => 'frontend',
                'client_secret' => 'my-super-secret-123',
                'network' => 'google',
                'code' => 'conflict',
                'redirect_uri' => 'http://localhost/callback',
            ]
        );
        self::assertEquals(400, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArraySubset([
            'hint' => 'Пользователь с таким email уже существует. Войдите обычным способом и привяжите аккаунт в настройках профиля.',
        ], $data);
    }
}

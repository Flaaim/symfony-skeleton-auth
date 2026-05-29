<?php

declare(strict_types=1);

namespace Tests\Functional\Oauth;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\ArraySubsetAssertTrait;
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
        self::markTestIncomplete();
        $this->client->request('GET', '/authorize');

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPageWithoutChallenge(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'GET',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'frontend',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'scope' => 'common',
                'state' => 'sTaTe',
            ])
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testPageWithChallenge(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'GET',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'frontend',
                'code_challenge' => PKCE::challenge(PKCE::verifier()),
                'code_challenge_method' => 'S256',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'scope' => 'common',
                'state' => 'sTaTe',
            ])
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotEmpty($content = (string)$this->client->getResponse()->getContent());
        self::assertStringContainsString('<title>Авторизация</title>', $content);
    }

    public function testInvalidClient(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'GET',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'invalid',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'code_challenge' => PKCE::challenge(PKCE::verifier()),
                'code_challenge_method' => 'S256',
                'scope' => 'common',
                'state' => 'sTaTe',
            ])
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($content);

        self::assertArraySubset([
            'error' => 'invalid_client',
        ], $data);
    }

    public function testAuthActiveUser(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'POST',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'frontend',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'code_challenge' => PKCE::challenge(PKCE::verifier()),
                'code_challenge_method' => 'S256',
                'scope' => 'common',
                'state' => 'sTaTe',
            ]),
            [
                'email' => 'aCTive@app.test',
                'password' => 'password',
            ]
        );

        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
        self::assertNotEmpty($location = $this->client->getResponse()->getHeaderLine('Location'));

        /** @var array{query:string} $url */
        $url = parse_url($location);

        self::assertNotEmpty($url['query']);

        /** @var array{code:string,state:string} $query */
        parse_str($url['query'], $query);

        self::assertArrayHasKey('code', $query);
        self::assertNotEmpty($query['code']);
        self::assertArrayHasKey('state', $query);
        self::assertEquals('sTaTe', $query['state']);
    }

    public function testAuthWaitUser(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'POST',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'frontend',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'code_challenge' => PKCE::challenge(PKCE::verifier()),
                'code_challenge_method' => 'S256',
                'scope' => 'common',
                'state' => 'sTaTe',
            ]),
            [
                'email' => 'wait@app.test',
                'password' => 'password',
            ]
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertNotEmpty($content = (string)$this->client->getResponse()->getContent());
        self::assertStringContainsString('User is not confirmed.', $content);
    }

    public function testAuthInvalidUser(): void
    {
        self::markTestIncomplete();

        $this->client->request(
            'POST',
            '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => 'frontend',
                'redirect_uri' => 'http://localhost:8080/oauth',
                'code_challenge' => PKCE::challenge(PKCE::verifier()),
                'code_challenge_method' => 'S256',
                'scope' => 'common',
                'state' => 'sTaTe',
            ]),
            [
                'email' => 'active@app.test',
                'password' => '',
            ]
        );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertNotEmpty($content = (string)$this->client->getResponse()->getContent());
        self::assertStringContainsString('Incorrect email or password.', $content);
    }
}

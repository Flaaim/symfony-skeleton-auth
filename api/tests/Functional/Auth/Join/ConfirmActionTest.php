<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\Join;

use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

final class ConfirmActionTest extends WebTestCase
{
    private  KernelBrowser $client;
    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([ConfirmFixture::class]);

    }

    public function testSuccess(): void
    {

        $this->client->jsonRequest('POST', '/v1/auth/confirm', [
            'token' => ConfirmFixture::VALID
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());
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

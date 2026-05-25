<?php

declare(strict_types=1);

namespace Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
final class NotFoundTest extends WebTestCase
{
    use ArraySubsetAssertTrait;

    public function testNotFound(): void
    {
        $client = self::createClient();
        $client->request('GET', '/not-found');

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        self::assertJson($body = $client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertArraySubset(['error' => [
            'code' => 404,
        ]], $data);
    }
}

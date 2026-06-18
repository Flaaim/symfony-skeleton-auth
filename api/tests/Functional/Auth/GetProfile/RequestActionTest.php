<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\GetProfile;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\OAuth\Entity\UserAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    private readonly UserRepository $users;
    private readonly User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);

        $this->authenticatedUser = $this->users->findByEmail(new Email(RequestFixture::EMAIL));
    }

    public function testUnauthorizedUser(): void
    {
        $this->client->jsonRequest('GET', '/v1/user/profile');
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals([
            'message' => 'Unauthorized. Please provide a valid Bearer token.',
        ], $data);
    }

    public function testSuccess(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUser->getId()->getValue()));

        $this->client->jsonRequest('GET', '/v1/user/profile');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = (string)$this->client->getResponse()->getContent());

        $data = Json::decode($body);
        self::assertEquals([
            'id' => RequestFixture::ID,
            'email' => RequestFixture::EMAIL,
            'networks' => [],
        ], $data);
    }
}

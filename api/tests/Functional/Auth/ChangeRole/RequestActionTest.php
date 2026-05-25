<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeRole;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Role;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\UserRoleChanged;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $users;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);
    }

    public function testAlready(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'userId' => RequestFixture::USER_ID,
            'role' => Role::USER,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Role is already assigned.'], $data);
    }

    public function testSuccess(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'userId' => RequestFixture::USER_ID,
            'role' => Role::TEACHER,
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::USER_ID));

        self::assertEquals(Role::TEACHER, $user->getRole()->getName());

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();

        self::assertInstanceOf(UserRoleChanged::class, $message);

        self::assertEquals(RequestFixture::USER_ID, $message->id);
        self::assertEquals(Role::TEACHER, $message->role);
    }

    public function testNotFound(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'userId' => 'c2cfad53-23dd-4817-8c2d-944b4c0101f1',
            'role' => Role::TEACHER,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User is not found.'], $data);
    }

    public function testInvalid(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'userId' => 'invalid-user-id',
            'role' => 'invalid-role',
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'role' => 'The role must be a valid role.',
            'id' => 'This is not a valid UUID.',
        ]], $data);
    }

    public function testEmpty(): void
    {
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change');

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'role' => 'The role must be a valid role.',
            'id' => 'This value should not be blank.',
        ]], $data);
    }
}

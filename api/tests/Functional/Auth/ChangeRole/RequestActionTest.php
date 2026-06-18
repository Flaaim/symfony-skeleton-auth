<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeRole;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\Role;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\UserRoleChanged;
use App\OAuth\Entity\UserAdapter;
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
    private readonly KernelBrowser $client;
    private readonly UserRepository $users;
    private readonly User $authenticatedUser;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);

        $this->authenticatedUser = $this->users->findByEmail(new Email(RequestFixture::USER_EMAIL));
    }

    public function testAlready(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'role' => Role::USER,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Role is already assigned.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUser->getId()->getValue()));
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
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

    public function testInvalid(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change', [
            'role' => 'invalid-role',
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'role' => 'The role must be a valid role.',
        ]], $data);
    }

    public function testEmpty(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/role/change');
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'role' => 'The role must be a valid role.',
        ]], $data);
    }
}

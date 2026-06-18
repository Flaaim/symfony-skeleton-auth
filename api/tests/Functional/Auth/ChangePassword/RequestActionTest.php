<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
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

    private readonly User $authenticatedByEmailUser;

    private readonly User $authenticatedByNetworkUser;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);

        $this->authenticatedByEmailUser = $this->users->findByEmail(new Email(RequestFixture::EMAIL));
        $this->authenticatedByNetworkUser = $this->users->findByEmail(new Email(RequestFixture::JOIN_BY_GOOGLE['email']));
    }

    public function testOldPasswordNotFound(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedByNetworkUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'old_password' => 'hashedPassword',
            'new_password' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User does not have an old password.'], $data);
    }

    public function testIncorrectCurrentPassword(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedByEmailUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'old_password' => 'Incorrect',
            'new_password' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect current password.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedByEmailUser->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'old_password' => RequestFixture::PASSWORD,
            'new_password' => 'newPassword',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::USER_ID));

        self::assertTrue(password_verify('newPassword', $user->getPasswordHash()));
    }
}

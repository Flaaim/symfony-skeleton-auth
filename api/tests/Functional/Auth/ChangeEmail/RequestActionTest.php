<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangeEmail;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\User;
use App\Auth\Entity\User\UserRepository;
use App\Auth\Event\ChangeEmailRequested;
use App\Auth\Service\Tokenizer;
use App\Infrastructure\Doctrine\Flusher;
use App\OAuth\Entity\UserAdapter;
use DateTimeImmutable;
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
    private readonly User $authenticatedUserValid;
    private readonly Flusher $flusher;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->flusher = new Flusher($em);

        $this->users = new UserRepository($em);

        $this->authenticatedUserValid = $this->users->findByEmail(new Email(RequestFixture::VALID['email']));
    }

    public function testEmailExists(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUserValid->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'email' => RequestFixture::EXISTS['email'],
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Email is already in use.'], $data);
    }

    public function testEmailTheSame(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUserValid->getId()->getValue()));
        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'email' => RequestFixture::VALID['email'],
        ]);
        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Email is already same.'], $data);
    }

    public function testRequestAlready(): void
    {
        $user = $this->users->findByEmail(new Email(RequestFixture::VALID['email']));
        $user->requestEmailChanging(
            new Tokenizer('PT1H')->generate(new DateTimeImmutable('+1 day')),
            new DateTimeImmutable(),
            new Email('another@email.ru')
        );

        $this->flusher->flush();

        $testUser = new UserAdapter($this->authenticatedUserValid->getId()->getValue());
        $this->client->loginUser($testUser);

        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'email' => 'another@email.ru',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Changing is already requested.'], $data);
        $user = $this->users->get(new Id(RequestFixture::VALID['userId']));

        self::assertNotNull($user->getNewEmailToken());
    }

    public function testSuccess(): void
    {
        $this->client->loginUser(new UserAdapter($this->authenticatedUserValid->getId()->getValue()));
        /** @var InMemoryTransport $transport */
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('PUT', '/v1/auth/email/change/request', [
            'email' => 'some@email.ru',
        ]);

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::VALID['userId']));
        self::assertNotNull($user->getNewEmailToken());

        self::assertCount(1, $transport->getSent());

        $message = $transport->getSent()[0]->getMessage();
        self::assertInstanceOf(ChangeEmailRequested::class, $message);

        self::assertEquals($user->getNewEmail()->getValue(), $message->newEmail);
        self::assertEquals($user->getNewEmailToken()->getValue(), $message->newEmailToken);
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use App\Auth\Event\ChangeEmailRequested;
use App\Auth\Event\JoinByEmailRequested;
use App\Auth\Event\NetworkAttached;
use App\Auth\Event\PasswordReset;
use App\Auth\Event\PasswordResetRequested;
use App\Auth\Event\UserCreated;
use App\Auth\Event\UserJoinConfirmed;
use App\Auth\Event\UserRemoved;
use App\Auth\Event\UserRoleChanged;
use App\Auth\Service\PasswordHasher;
use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
final class User implements AggregateRoot
{
    use EventTrait;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $passwordHash = null;
    #[ORM\Embedded(class: Token::class, columnPrefix: 'join_confirm_token_')]
    private ?Token $joinConfirmToken = null;
    #[ORM\OneToMany(targetEntity: Network::class, mappedBy: 'user', cascade: ['ALL'], orphanRemoval: true)]
    private Collection $networks;
    #[ORM\Embedded(class: Token::class, columnPrefix: 'password_reset_token_')]
    private ?Token $passwordResetToken = null;
    #[ORM\Column(type: 'user_email', nullable: true)]
    private ?Email $newEmail = null;
    #[ORM\Embedded(class: Token::class, columnPrefix: 'new_email_token_')]
    private ?Token $newEmailToken = null;
    #[ORM\Column(type: 'user_role')]
    private Role $role;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'auth_user_id')]
        private Id $id,
        #[ORM\Column(type: 'datetime_immutable')]
        private DateTimeImmutable $date,
        #[ORM\Column(type: 'user_email')]
        private Email $email,
        #[ORM\Column(type: 'user_status')]
        private Status $status,
    ) {
        $this->networks = new ArrayCollection();
        $this->role = Role::user();
    }

    public static function requestJoinByEmail(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        string $passwordHash,
        Token $token
    ): self {
        $user = new self($id, $date, $email, Status::wait());
        $user->passwordHash = $passwordHash;
        $user->joinConfirmToken = $token;

        $user->recordEvent(new JoinByEmailRequested(
            $token->getValue(),
            $email->getValue()
        ));
        $user->recordEvent(new UserCreated($email->getValue()));
        return $user;
    }

    public static function joinByNetwork(
        Id $id,
        DateTimeImmutable $date,
        Email $email,
        string $network,
        string $identity
    ) {
        $user = new self($id, $date, $email, Status::active());
        $user->networks->add(new Network($user, $network, $identity));

        $user->recordEvent(new UserCreated($email->getValue()));

        return $user;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getJoinConfirmToken(): ?Token
    {
        return $this->joinConfirmToken;
    }

    public function attachNetwork(string $network, string $identity): void
    {
        $newNetwork = new Network($this, $network, $identity);
        /** @var Network $existing */
        foreach ($this->networks as $existing) {
            if ($existing->isEqualTo($newNetwork)) {
                throw new DomainException('Network is already attached.');
            }
        }
        $this->networks->add($newNetwork);

        $this->recordEvent(new NetworkAttached($network, $identity));
    }

    public function requestPasswordReset(Token $token, DateTimeImmutable $date): void
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }
        if (null !== $this->passwordResetToken && !$this->passwordResetToken->isExpiredTo($date)) {
            throw new DomainException('Resetting is already requested.');
        }

        $this->passwordResetToken = $token;

        $this->recordEvent(new PasswordResetRequested(
            $this->email->getValue(),
            $token->getValue(),
        ));
    }

    public function getPasswordResetToken(): ?Token
    {
        return $this->passwordResetToken;
    }

    public function resetPassword(string $token, DateTimeImmutable $date, string $hash): void
    {
        if (null === $this->passwordResetToken) {
            throw new DomainException('Resetting is not requested.');
        }
        $this->passwordResetToken->validate($token, $date);
        $this->passwordResetToken = null;
        $this->passwordHash = $hash;

        $this->recordEvent(new PasswordReset($this->id->getValue()));
    }

    public function changePassword(string $current, string $new, PasswordHasher $hasher): void
    {
        if (null === $this->passwordHash) {
            throw new DomainException('User does not have an old password.');
        }
        if (!$hasher->validate($current, $this->passwordHash)) {
            throw new DomainException('Incorrect current password.');
        }
        $this->passwordHash = $hasher->hash($new);
    }

    public function requestEmailChanging(Token $token, DateTimeImmutable $date, Email $email): void
    {
        if (!$this->isActive()) {
            throw new DomainException('User is not active.');
        }
        if ($this->email->isEqualTo($email)) {
            throw new DomainException('Email is already same.');
        }
        if (null !== $this->newEmailToken && !$this->newEmailToken->isExpiredTo($date)) {
            throw new DomainException('Changing is already requested.');
        }
        $this->newEmail = $email;
        $this->newEmailToken = $token;

        $this->recordEvent(
            new ChangeEmailRequested(
                $this->newEmail->getValue(),
                $this->newEmailToken->getValue()
            )
        );
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function confirmJoin(string $token, DateTimeImmutable $date): void
    {
        if (null === $this->joinConfirmToken) {
            throw new DomainException('Confirmation is not required.');
        }
        $this->joinConfirmToken->validate($token, $date);
        $this->status = Status::active();
        $this->joinConfirmToken = null;

        $this->recordEvent(new UserJoinConfirmed($this->email->getValue()));
    }

    public function confirmEmailChanging(string $token, DateTimeImmutable $date): void
    {
        if (null === $this->newEmail || null === $this->newEmailToken) {
            throw new DomainException('Changing is not requested.');
        }
        $this->newEmailToken->validate($token, $date);
        $this->email = $this->newEmail;
        $this->newEmail = null;
        $this->newEmailToken = null;
    }

    public function changeRole(Role $role): void
    {
        if ($this->role->isEqualTo($role)) {
            throw new DomainException('Role is already assigned.');
        }
        $this->role = $role;

        $this->recordEvent(new UserRoleChanged($this->id->getValue(), $this->getRole()->getName()));
    }

    public function remove(): void
    {
        if (!$this->isWait()) {
            throw new DomainException('Unable to remove active user.');
        }
        $this->recordEvent(new UserRemoved($this->id->getValue()));
    }

    public function getNewEmail(): ?Email
    {
        return $this->newEmail;
    }

    public function getNewEmailToken(): ?Token
    {
        return $this->newEmailToken;
    }

    /**
     * @return Network[]
     */
    public function getNetworks(): array
    {
        /** @var Network[] */
        return $this->networks->toArray();
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    #[ORM\PostLoad]
    public function checkEmbeddables(): void
    {
        if (null !== $this->joinConfirmToken && $this->joinConfirmToken->isEmpty()) {
            $this->joinConfirmToken = null;
        }
        if (null !== $this->passwordResetToken && $this->passwordResetToken->isEmpty()) {
            $this->passwordResetToken = null;
        }
        if (null !== $this->newEmailToken && $this->newEmailToken->isEmpty()) {
            $this->newEmailToken = null;
        }
    }
}

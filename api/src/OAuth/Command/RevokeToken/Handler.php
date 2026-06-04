<?php

declare(strict_types=1);

namespace App\OAuth\Command\RevokeToken;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Exception;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class Handler
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        #[Autowire(env: 'JWT_ENCRYPTION_KEY')]
        private readonly string $encryptionKey,
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {}

    public function handle(string $token): void
    {
        try {
            $keyContent = $this->encryptionKey;

            if (is_file($keyContent)) {
                $keyContent = file_get_contents($keyContent);
                if (false === $keyContent) {
                    throw new Exception('Unable to read key file.');
                }
            }
            $cleanToken = trim($token);
            try {
                $key = Key::loadFromAsciiSafeString($keyContent);
                $decrypted = Crypto::decrypt($cleanToken, $key);
                $payload = json_decode($decrypted, true);
            } catch (Exception $e) {
                $decrypted = Crypto::decryptWithPassword($cleanToken, $keyContent);
                $payload = json_decode($decrypted, true);
            }
            if (\is_array($payload) && isset($payload['refresh_token_id'])) {
                $this->refreshTokenRepository->revokeRefreshToken($payload['refresh_token_id']);
            }
        } catch (Exception $e) {
        }
    }
}

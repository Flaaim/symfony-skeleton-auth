<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\AttachNetwork;

use App\Auth\Command\AttachNetwork\Command;
use App\Auth\Command\AttachNetwork\Handler;
use App\Infrastructure\Http\Validator\Validator;
use App\Infrastructure\Social\Registry\ClientRegistryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly ClientRegistryInterface $socialClientRegistry,
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/auth/network/attach', name: 'auth.network.attach', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $currentUser = $this->security->getUser();
        if (null === $currentUser) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $currentUserId = $currentUser->getUserIdentifier();

        $body = $request->toArray();

        $network = (string)($body['network'] ?? '');
        $code = (string)($body['code'] ?? '');
        $redirectUri = (string)($body['redirect_uri'] ?? '');

        if ('' === $network || '' === $code || '' === $redirectUri) {
            return new JsonResponse(['error' => 'Network, code or redirect uri are required.'], Response::HTTP_BAD_REQUEST);
        }

        $socialUser = $this->socialClientRegistry->create($code, $network, $redirectUri);

        $command = new Command($currentUserId, $socialUser->network, $socialUser->identity);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}

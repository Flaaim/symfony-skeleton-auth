<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\AttachNetwork;

use App\Auth\Command\AttachNetwork\Command;
use App\Auth\Command\AttachNetwork\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Infrastructure\Social\Registry\ClientRegistry;

final class RequestAction
{
    public function __construct(
        private readonly ClientRegistry $socialClientRegistry,
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/v1/auth/network/attach', name: 'auth.network.attach', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $currentUser = $this->security->getUser();
        if($currentUser === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $currentUserId = $currentUser->getUserIdentifier();
        $body = $request->toArray();

        $network = (string)($body['network'] ?? '');
        $code = (string)($body['code'] ?? '');

        if($network === '' || $code === '') {
            return new JsonResponse(['error' => 'Network and code are required'], Response::HTTP_BAD_REQUEST);
        }

        try{
            $socialUser = $this->socialClientRegistry->create($code, $network);

            $command = new Command($currentUserId, $socialUser->network, $socialUser->identity);

            $this->validator->validate($command);

            $this->handler->handle($command);

        }catch (\DomainException $e){
            $this->logger->warning('Network attach domain conflict: {message}', [
                'message' => $e->getMessage(),
                'userId' => $currentUserId,
                'network' => $network
            ]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }catch (\Throwable $e){
            $this->logger->error('Network attach system crash: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
                'userId' => $currentUserId
            ]);
            return new JsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Network attached successfully'], Response::HTTP_OK);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangeRole;

use App\Auth\Command\ChangeRole\Command;
use App\Auth\Command\ChangeRole\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/auth/user/role/change', name: 'auth.user.role.change', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        $currentUser = $this->security->getUser();
        if($currentUser === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $userId = $currentUser->getUserIdentifier();
        $body = $request->toArray();
        $role = (string)($body['role'] ?? '');

        $command = new Command($userId, $role);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

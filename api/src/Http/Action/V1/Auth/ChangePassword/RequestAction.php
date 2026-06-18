<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangePassword;

use App\Auth\Command\ChangePassword\Command;
use App\Auth\Command\ChangePassword\Handler;
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

    #[Route('/v1/auth/user/password/change', 'auth.user.password.change', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        $currentUser = $this->security->getUser();
        if (null === $currentUser) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $body = $request->toArray();

        $userId = $currentUser->getUserIdentifier();
        $currentPassword = (string)($body['old_password'] ?? '');
        $newPassword = (string)($body['new_password'] ?? '');

        $command = new Command($userId, $currentPassword, $newPassword);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

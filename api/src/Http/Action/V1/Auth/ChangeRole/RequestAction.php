<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangeRole;

use App\Auth\Command\ChangeRole\Command;
use App\Auth\Command\ChangeRole\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator
    ) {}

    #[Route('/v1/auth/user/change-role', name: 'auth.user.change-role', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();
        $userId = (string)($body['userId'] ?? '');
        $role = (string)($body['role'] ?? '');

        $command = new Command($userId, $role);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\Remove;

use App\Auth\Command\Remove\Command;
use App\Auth\Command\Remove\Handler;
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

    #[Route('/v1/auth/user/remove', name: 'auth.user.remove', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();
        $userId = (string)($body['userId'] ?? '');

        $command = new Command($userId);
        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

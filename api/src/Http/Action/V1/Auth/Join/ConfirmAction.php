<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\Join;

use App\Auth\Command\JoinByEmail\Confirm\Command;
use App\Auth\Command\JoinByEmail\Confirm\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConfirmAction
{
    public function __construct(
        private readonly Handler $handler,
    ) {}

    #[Route('/v1/auth/confirm', name: 'auth_confirm', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();
        $token = (string) ($body['token'] ?? '');

        $command = new Command($token);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

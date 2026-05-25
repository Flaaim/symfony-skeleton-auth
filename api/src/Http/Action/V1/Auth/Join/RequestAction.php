<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\Join;

use App\Auth\Command\JoinByEmail\Request\Command;
use App\Auth\Command\JoinByEmail\Request\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
    ) {}

    #[Route('/v1/auth/join/request', name: 'auth.join.request', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();
        $email = (string)($body['email'] ?? '');
        $password = (string)($body['password'] ?? '');

        $command = new Command($email, $password);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new Response(null, Response::HTTP_CREATED);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangeEmail;

use App\Auth\Command\ChangeEmail\Confirm\Command;
use App\Auth\Command\ChangeEmail\Confirm\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConfirmAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator
    ) {}

    #[Route('/v1/auth/email/change/confirm', name: 'auth.email.change.confirm', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();

        $token = (string)($body['token'] ?? '');
        $command = new Command($token);
        $this->validator->validate($command);

        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

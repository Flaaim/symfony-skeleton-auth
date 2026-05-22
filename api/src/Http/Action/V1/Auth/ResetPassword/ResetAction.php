<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ResetPassword;


use App\Auth\Command\ResetPassword\Reset\Command;
use App\Auth\Command\ResetPassword\Reset\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResetAction
{
    public function __construct(
        private readonly Handler  $handler,
        private readonly Validator $validator
    ) {}

    #[Route('/v1/auth/password/reset', name: 'auth.password.reset', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();
        $password = (string) ($body['password'] ?? '');
        $token = (string) ($body['token'] ?? '');

        $command = new Command($token, $password);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

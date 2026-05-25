<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\JoinByNetwork;

use App\Auth\Command\JoinByNetwork\Command;
use App\Auth\Command\JoinByNetwork\Handler;
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

    #[Route('/v1/auth/join/network/request', name: 'auth.join.network.request', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();

        $email = (string)($body['email'] ?? '');
        $network = (string)($body['network'] ?? '');
        $identity = (string)($body['identity'] ?? '');

        $command = new Command($email, $network, $identity);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_CREATED);
    }
}

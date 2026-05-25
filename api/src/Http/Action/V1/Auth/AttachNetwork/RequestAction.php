<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\AttachNetwork;

use App\Auth\Command\AttachNetwork\Command;
use App\Auth\Command\AttachNetwork\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator
    ) {}

    #[Route('/v1/auth/join/network/attach', name: 'auth.join.network.attach', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();

        $email = (string)($body['email'] ?? '');
        $network = (string)($body['network'] ?? '');
        $identity = (string)($body['identity'] ?? '');

        $command = new Command($email, $network, $identity);
        $this->validator->validate($command);
        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}

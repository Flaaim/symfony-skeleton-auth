<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangeEmail;

use App\Auth\Command\ChangeEmail\Request\Command;
use App\Auth\Command\ChangeEmail\Request\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Bundle\SecurityBundle\Security;
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

    #[Route('/v1/auth/email/change/request', name: 'auth.email.change.request', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        $user = $this->security->getUser();
        if (null === $user) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        $userId = $user->getUserIdentifier();
        $body = $request->toArray();

        $email = (string)($body['email'] ?? '');

        $command = new Command($userId, $email);

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\GetProfile;

use App\Auth\Query\GetProfile\Query;
use App\Auth\Query\GetProfile\QueryHandler;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/user/profile', name: 'user.profile', methods: ['GET'])]
final class RequestAction
{
    public function __construct(
        private readonly QueryHandler $handler,
        private readonly Security $security,
    ) {}

    public function __invoke(): Response
    {
        /** @var UserAdapter|null $currentUser */
        $currentUser = $this->security->getUser();

        if (null === $currentUser) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $currentUser->getUserIdentifier();

        $query = new Query($userId);

        $profile = $this->handler->handle($query);

        return new JsonResponse($profile, Response::HTTP_OK);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\GetProfile;

use App\Auth\Query\GetProfile\Fetcher;
use App\Auth\Query\GetProfile\Query;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/user/profile', name: 'user.profile', methods: ['GET'])]
final class RequestAction
{
    public function __construct(
        private readonly Fetcher  $fetcher,
        private readonly Security $security,
    ) {}

    public function __invoke(): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $query = new Query($userId);

        $profile = $this->fetcher->fetch($query);

        return new JsonResponse($profile, Response::HTTP_OK);
    }
}

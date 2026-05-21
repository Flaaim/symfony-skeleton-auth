<?php

declare(strict_types=1);

namespace App\Http\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeAction
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        return new Response('{}', 200, ['Content-Type' => 'application/json']);
    }
}

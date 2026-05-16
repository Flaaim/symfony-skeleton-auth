<?php

declare(strict_types=1);

namespace Http\Action;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeAction
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(ServerRequestInterface $_request): ResponseInterface
    {
        $response = new Response(200);
        $response->getBody()->write('{}');

        return $response->withHeader('Content-Type', 'application/json');
    }
}

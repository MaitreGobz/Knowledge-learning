<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PingController extends AbstractController
{
    #[OA\Get(
        path: "/api/ping",
        summary: "Ping API Health Check",
        tags: ["System"],
        responses: [
            new OA\Response(
                response: 200,
                description: "API is healthy",
            )
        ]
    )]
    #[Route(path: '/api/ping', name: 'api_ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'service' => 'Knowledge Learning API',
        ]);
    }
}
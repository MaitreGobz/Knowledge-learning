<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PingController extends AbstractController
{
    #[Route('/api/ping', name: 'api_ping', methods: ['GET'])]
    #[OA\Get(
        path: '/api/ping',
        summary: 'Ping API Health Check',
        tags: ['System']
    )]

    #[OA\Response(
        response: 200,
        description: 'API is healthy'
    )]

    /**
     * Simple health check endpoint.
     */
    public function ping(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'service' => 'Knowledge Learning API',
        ]);
    }
}

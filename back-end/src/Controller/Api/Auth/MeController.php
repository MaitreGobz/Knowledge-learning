<?php

namespace App\Controller\Api\Auth;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoint to get information about the currently authenticated user
 */
final class MeController extends AbstractController
{
    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Récupérer les informations de l\'utilisateur connecté',
        tags: ['Auth']
    )]

    #[OA\Response(
        response: 200,
        description: 'Informations de l\'utilisateur',
        content: new OA\JsonContent(
            type: 'object',
            required: ['id', 'email', 'roles'],
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['ROLE_USER']
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]

    /**
     * Handle the request to get the current authenticated user's information
     */
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            // Not connected => 401
            return $this->json(['authenticated' => false], 401);
        }

        // Connected
        return $this->json([
            'authenticated' => true,
            'user' => [
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
        ], 200);
    }
}

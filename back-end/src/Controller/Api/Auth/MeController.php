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
        description: 'Retourne les informations de l\'utilisateur connecté.',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Informations de l\'utilisateur',
                content: new OA\JsonContent(
                    required: ['id', 'email', 'roles'],
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ],
    )]
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
                'email' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ],
        ], 200);
    }
}

<?php

namespace App\Controller\Api\Auth;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


/**
 * Authentication-related API endpoints
 */
final class AuthController extends AbstractController
{
    #[Route('/api/auth/csrf', name: 'api_auth_csrf', methods: ['GET'])]
    #[OA\Get(
        path: '/api/auth/csrf',
        summary: 'Récupérer un token CSRF pour la SPA Angular',
        description: 'Retourne un token CSRF à utiliser dans le header X-CSRF-TOKEN pour les requêtes d\'écriture (POST/PUT/PATCH/DELETE)',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token CSRF généré',
                content: new OA\JsonContent(
                    required: ['csrfToken'],
                    properties: [
                        new OA\Property(
                            property: 'csrfToken',
                            type: 'string',
                            example: 'Zk9sMTRyV2Q5T1p...'
                        )
                    ]
                )
            )
        ]
    )]
    public function csrf(Request $request, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        // Ensure session is started
        $request->getSession()->start();

        // Generate a CSRF token associated with the "auth" token ID
        $token = $csrfTokenManager->getToken('auth')->getValue();

        // Return the token as JSON so it can be used by the front-end
        return $this->json([
            'csrfToken' => $token,
        ]);
    }

    #[Route('/api/auth/login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Connexion (session cookie)',
        description: 'Authentifie un utilisateur et établit une session cookie. ',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Identifiants invalides'),
            new OA\Response(response: 403, description: 'Email non vérifié'),
        ],
        tags: ['Auth']
    )]
    public function loginDoc(): JsonResponse
    {
        // // The authentication process is handled by Symfony Security
        return $this->json(['message' => 'Géré par Symfony Security json_login.']);
    }
}

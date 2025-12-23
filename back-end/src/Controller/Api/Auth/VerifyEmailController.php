<?php

namespace App\Controller\Api\Auth;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[OA\Tag(name: 'Auth')]
final class VerifyEmailController extends AbstractController
{
    #[Route('/api/auth/verify-email', name: 'api_auth_verify_email', methods: ['GET'])]
    #[OA\Get(
        path: '/api/auth/verify-email',
        operationId: 'authVerifyEmail',
        summary: 'Vérifier un compte via URL signée',
        description: 'Valide le compte utilisateur à partir d\'une URL signée envoyée par email.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID utilisateur',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 123
            ),
            new OA\Parameter(name: 'signature', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'expires', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'hash', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Compte vérifié'),
            new OA\Response(
                response: 404,
                description: 'Utilisateur introuvable',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(
                response: 400,
                description: 'Lien invalide ou expiré',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
        ]
    )]
    public function __invoke(
        Request $request,
        UserRepository $users,
        EmailVerifier $emailVerifier
    ): JsonResponse {
        // Retrieve user id from query parameters
        $id = $request->query->get('id');
        // Defensive check: missing user id
        if (!$id) {
            return $this->json(['message' => 'Id Introuvable'], 400);
        }

        // Fetch user from database
        $user = $users->find($id);

        // If user does not exist, return 404
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        try {
            // Validate the signed URL and activate the account
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $e) {
            // Any verification failure results in a generic error
            return $this->json(['message' => 'Lien de vérification invalide ou expiré'], 400);
        }

        // 204 No Content: verification succeeded
        return new JsonResponse(null, 204);
    }
}

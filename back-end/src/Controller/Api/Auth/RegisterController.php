<?php

namespace App\Controller\Api\Auth;

use App\Dto\Auth\RegisterRequest;
use App\Service\Auth\RegisterUserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Auth')]
final class RegisterController extends AbstractController
{
    /**
     * User registration endpoint.
     *
     * This endpoint creates a new user account with a pending verification status.
     * The account must be activated via an email link before it can be used.
     */
    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Inscription utilisateur',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@test.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'User123!'),
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Utilisateur créé (en attente de vérification)',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 123),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@test.com'),
                new OA\Property(property: 'status', type: 'string', example: 'PENDING_VERIFICATION'),
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Email déjà existant',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Email déjà existant.')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    example: [
                        'email' => ["L'email doit être valide"],
                        'password' => ["Le mot de passe doit contenir au moins 8 caractères"],
                        'emptyFields' => ["Tous les champs doivent être remplis"]
                    ]
                )
            ]
        )
    )]

    /**
     * Handles user registration requests.
     */
    public function __invoke(
        Request $request,
        ValidatorInterface $validator,
        RegisterUserService $service
    ): JsonResponse {
        // Decode JSON payload into an associative array
        $data = json_decode($request->getContent(), true);

        //If the payload is not valid JSON, return an error
        if (!is_array($data)) {
            return $this->json([
                'errors' => [
                    'payload' => ['Invalid JSON payload.'],
                ],
            ], 422);
        }

        // Create and hydrate the DTO with controlled input
        $dto = new RegisterRequest();
        $dto->email = $data['email'] ?? null;
        $dto->password = $data['password'] ?? null;

        // Validate the DTO usingSymfony validator
        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            //Convert validation violation into a structured API error
            return $this->json($this->formatViolations($violations), 422);
        }
        try {
            // Call register logic
            $user = $service->register($dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        //Successful creation response
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'status' => 'PENDING_VERIFICATION',
            'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre email pour activer votre compte.'
        ], 201);
    }

    /**
     * Formats Symfony validation violations into a standardized API error structure.
     * 
     * Example output:
     * {
     *   "errors": {
     *      "email": ["Adresse email invalide"],
     *      "password": ["Le mot de passe doit contenir au minimum 8 caractères"],
     *      "emptyFields: ["Tous les champs doivent être remplis"]
     *   }
     * }
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $v) {
            $field = (string) $v->getPropertyPath();
            $message = (string) $v->getMessage();

            // Custom mapping for global "empty fields" error
            if ($message === 'Tous les champs doivent être remplis') {
                $errors['emptyFields'][] = $message;
                continue;
            }

            // Fallback for violations not bound to a specific field
            if ($field === '') {
                $errors['general'][] = $message;
                continue;
            }

            // Standard field-based validation error
            $errors[$field][] = $message;
        }

        return ['errors' => $errors];
    }
}

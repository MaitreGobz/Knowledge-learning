<?php

namespace App\Controller\Api\Admin;

use OpenApi\Attributes as OA;
use App\Entity\User;
use App\Dto\Admin\UserAdminRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Admin user management API endpoints
 */
final class UserAdminController extends AbstractController
{
    // --- READ ---
    // - List users
    // - Detail user
    #[Route('/api/admin/users', name: 'api_admin_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Lister les utilisateurs (admin)',
        tags: ['Admin - Users']
    )]

    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20))]
    #[OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'createdAt'))]
    #[OA\Parameter(name: 'order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc'))]
    #[OA\Parameter(name: 'email', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'isVerified', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'))]

    #[OA\Response(
        response: 200,
        description: 'Liste paginée des utilisateurs',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'email', type: 'string', example: 'admin@example.com'),
                            new OA\Property(property: 'isVerified', type: 'boolean', example: true),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-12-30T10:00:00+00:00'),
                        ]
                    )
                ),
                new OA\Property(
                    property: 'meta',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'page', type: 'integer', example: 1),
                        new OA\Property(property: 'limit', type: 'integer', example: 20),
                        new OA\Property(property: 'totalItems', type: 'integer', example: 42),
                        new OA\Property(property: 'totalPages', type: 'integer', example: 3),
                        new OA\Property(property: 'sort', type: 'string', example: 'createdAt'),
                        new OA\Property(property: 'order', type: 'string', example: 'desc'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]

    /**
     * List users with pagination, sorting, and filtering
     */
    public function listUsers(Request $request, UserRepository $users): JsonResponse
    {
        //Pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        // Prevent abusive queries
        $limit = min(100, max(1, $limit));

        // Filters
        $email = $request->query->get('email');
        $isVerified = $request->query->get('isVerified');

        // Sorting
        $sort = (string) $request->query->get('sort', 'createdAt');
        $order = strtolower((string) $request->query->get('order', 'desc'));
        $allowedSort = ['id', 'email', 'createdAt', 'isVerified'];

        if (!in_array($sort, $allowedSort, true)) {
            return $this->json(['message' => 'Invalid sort field.'], 400);
        }
        if (!in_array($order, ['asc', 'desc'], true)) {
            return $this->json(['message' => 'Invalid order value.'], 400);
        }

        // Normalized isVerified
        $isVerifiedBool = null;
        if ($isVerified !== null) {
            $val = strtolower((string) $isVerified);
            $isVerifiedBool = in_array($val, ['1', 'true', 'yes'], true) ? true : (in_array($val, ['0', 'false', 'no'], true) ? false : null);
        }

        // Fetch paginated users with filters
        $paginator = $users->searchPaginated(
            page: $page,
            limit: $limit,
            email: is_string($email) ? $email : null,
            isVerified: $isVerifiedBool,
            sort: $sort,
            order: $order
        );

        // Total items depends on repository implementation
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        // Map entities to a simple array
        $items = [];
        foreach ($paginator as $user) {
            $items[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
                'createdAt' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        // Final JSON response
        return $this->json([
            'items' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'sort' => $sort,
                'order' => $order,
            ]
        ]);
    }

    #[Route('/api/admin/users/{id}', name: 'api_admin_users_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/users/{id}',
        summary: "Détail d'un utilisateur (admin)",
        tags: ['Admin - Users']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant de l'utilisateur",
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Détail utilisateur",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                new OA\Property(property: 'isVerified', type: 'boolean', example: true),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-12-30T10:00:00+00:00'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]
    #[OA\Response(
        response: 404,
        description: "Utilisateur introuvable",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User not found.'),
            ]
        )
    )]
    public function detailUser(int $id, UserRepository $users): JsonResponse
    {
        $user = $users->find($id);

        if ($user === null) {
            return $this->json(['message' => 'User not found.'], 404);
        }
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
            'createdAt' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }

    // --- CREATE ---
    // - Create user
    #[Route('/api/admin/users', name: 'api_admin_users_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/admin/users',
        summary: 'Créer un utilisateur (admin)',
        tags: ['Admin - Users']
    )]
    #[OA\Parameter(
        name: 'X-CSRF-TOKEN',
        in: 'header',
        required: true,
        description: 'Token CSRF requis (session cookie). CSRF id: admin_create_user',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'new.user@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'P@ssw0rd123!'),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['ROLE_USER']
                ),
                new OA\Property(property: 'isActive', type: 'boolean', example: true),
                new OA\Property(property: 'isVerified', type: 'boolean', example: true),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur créé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 123),
                new OA\Property(property: 'email', type: 'string', example: 'new.user@example.com'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                new OA\Property(property: 'isActive', type: 'boolean', example: true),
                new OA\Property(property: 'isVerified', type: 'boolean', example: true),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-12-31T12:00:00+00:00'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', example: '2025-12-31T12:00:00+00:00'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Requête invalide (JSON/Champs manquants)',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid JSON.'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis) ou CSRF invalide')]
    #[OA\Response(
        response: 409,
        description: 'Email déjà utilisé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Email already exists.'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation échouée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed.'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    additionalProperties: true,
                    example: ['password' => 'Password is too weak.']
                )
            ]
        )
    )]

    /**
     * Create a new user
     */
    public function createUser(
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        CsrfTokenManagerInterface $csrfTokenManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // CSRF token validation (enabled in prod/dev, bypassed in test)
        if ($this->getParameter('kernel.environment') !== 'test') {
            $csrfValue = (string) $request->headers->get('X-CSRF-TOKEN', '');
            if ($csrfValue === '' || !$csrfTokenManager->isTokenValid(new CsrfToken('admin_create_user', $csrfValue))) {
                return $this->json(['message' => 'Invalid CSRF token.'], 403);
            }
        }

        // Decode and validate JSON payload
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid JSON.'], 400);
        }

        // Hydrate and normalize DTO
        $input = new UserAdminRequest();
        $input->email = isset($data['email']) ? (string) $data['email'] : null;
        $input->password = isset($data['password']) ? (string) $data['password'] : null;
        $input->roles = $data['roles'] ?? null;
        $input->isActive = array_key_exists('isActive', $data) ? (bool) $data['isActive'] : null;
        $input->isVerified = array_key_exists('isVerified', $data) ? (bool) $data['isVerified'] : null;
        $input->normalize();

        // Validate DTO
        $violations = $validator->validate($input);
        $errors = [];
        foreach ($violations as $v) {
            $field = (string) $v->getPropertyPath();
            $errors[$field][] = $v->getMessage();
        }

        // Roles validation
        $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        $rolesError = $input->validateRoles($allowedRoles);
        if ($rolesError !== null) {
            $errors['roles'] = $rolesError;
        }

        if (!empty($errors)) {
            return $this->json(['message' => 'Validation échouée.', 'errors' => $errors], 422);
        }

        // Unique email check
        $existing = $users->findOneBy(['email' => $input->email]);
        if ($existing !== null) {
            return $this->json(['message' => 'Email déjà existant.'], 409);
        }

        // Create new user
        $user = new User();
        $user->setEmail($input->email);

        if (is_array($input->roles) && $input->roles !== []) {
            $user->setRoles(array_values(array_unique(array_map('strval', $input->roles))));
        } else {
            $user->setRoles([]);
        }

        $user->setIsActive((bool) $input->isActive);
        $user->setIsVerified((bool) $input->isVerified);

        $user->setPassword($hasher->hashPassword($user, (string) $input->password));

        $em->persist($user);
        $em->flush();

        // Successful creation response
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isActive' => $user->isActive(),
            'isVerified' => $user->isVerified(),
            'createdAt' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ], 201);
    }
}

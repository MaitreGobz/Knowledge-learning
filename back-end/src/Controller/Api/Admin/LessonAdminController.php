<?php

namespace App\Controller\Api\Admin;

use OpenApi\Attributes as OA;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\User;
use App\Dto\Admin\LessonAdminCreateRequest;
use App\Dto\Admin\LessonAdminUpdateRequest;
use App\Repository\LessonRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 *  Admin lesson management API endpoints
 */
final class LessonAdminController extends AbstractController
{
    // --- READ ---
    // - List lessons
    // - Detail lesson
    #[Route('/api/admin/lessons', name: 'api_admin_lessons_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/lessons',
        summary: 'Lister les leçons (admin)',
        tags: ['Admin - Lessons']
    )]
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20))]
    #[OA\Response(
        response: 200,
        description: 'Liste paginée des leçons',
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
                            new OA\Property(property: 'title', type: 'string', example: 'Les outils du jardinier'),
                            new OA\Property(property: 'price', type: 'integer', example: 16),
                            new OA\Property(property: 'cursusId', type: 'string', example: "Cursus d'initiation au jardinage"),
                            new OA\Property(property: 'themeTitle', type: 'string', example: 'Jardinage'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-12-30T10:00:00+00:00'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', example: '2025-12-31T12:00:00+00:00'),
                            new OA\Property(property: 'createdBy', type: 'string', example: 'admin@example.com', nullable: true),
                            new OA\Property(property: 'updatedBy', type: 'string', example: 'admin@example.com', nullable: true),
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
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]

    /**
     * List lessons with pagination
     */
    public function listLessons(Request $request, LessonRepository $lessons): JsonResponse
    {
        // Pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);
        // Prevent abusive queries
        $limit = min(100, max(1, $limit));

        $paginator = $lessons->listPaginated($page, $limit);

        // Calculate total items and pages
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        // Map entities to a simple array
        $items = [];
        foreach ($paginator as $lesson) {
            $cursus = $lesson->getCursus();
            $theme = $cursus?->getTheme();

            $items[] = [
                'id' => $lesson->getId(),
                'title' => $lesson->getTitle(),
                'price' => $lesson->getPrice(),
                'content' => $lesson->getContent(),
                'cursusTitle' => $cursus?->getTitle(),
                'themeTitle' => $theme?->getTitle(),
                'createdAt' => $lesson->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'updatedAt' => $lesson->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
                'createdBy' => $lesson->getCreatedBy(),
                'updatedBy' => $lesson->getUpdatedBy(),
            ];
        }

        return $this->json([
            'items' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
            ]
        ]);
    }

    #[Route('/api/admin/lessons/{id}', name: 'api_admin_lessons_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/lessons/{id}',
        summary: "Détail d'une leçon (admin)",
        tags: ['Admin - Lessons']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant de la leçon",
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Détail leçon",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Les outils du jardinier'),
                new OA\Property(property: 'price', type: 'integer', example: 16),
                new OA\Property(property: 'content', type: 'string', example: 'Lorem ipsum...'),
                new OA\Property(property: 'position', type: 'integer', example: 1),
                new OA\Property(property: 'cursusTitle', type: 'string', example: "Cursus d'initiation au jardinage"),
                new OA\Property(property: 'themeTitle', type: 'string', example: 'Jardinage'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'createdBy', type: 'string', example: 'admin'),
                new OA\Property(property: 'updatedBy', type: 'string', example: 'admin'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis)')]
    #[OA\Response(
        response: 404,
        description: "Leçon introuvable",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Leçon introuvable.'),
            ]
        )
    )]

    /**
     * Get detailed info of a lesson
     */
    public function detailLesson(int $id, LessonRepository $lessons): JsonResponse
    {
        $lesson = $lessons->find($id);

        if ($lesson === null) {
            return $this->json(['message' => 'Leçon introuvable.'], 404);
        }

        $cursus = $lesson->getCursus();
        $theme = $cursus?->getTheme();

        return $this->json([
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'price' => $lesson->getPrice(),
            'content' => $lesson->getContent(),
            'position' => $lesson->getPosition(),
            'cursusTitle' => $cursus?->getTitle(),
            'themeTitle' => $theme?->getTitle(),
            'createdAt' => $lesson->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $lesson->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'createdBy' => $lesson->getCreatedBy(),
            'updatedBy' => $lesson->getUpdatedBy(),
        ]);
    }

    // --- CREATE ---
    #[Route('/api/admin/lessons', name: 'api_admin_lessons_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/admin/lessons',
        summary: 'Créer une leçon (admin)',
        tags: ['Admin - Lessons']
    )]
    #[OA\Parameter(
        name: 'X-CSRF-TOKEN',
        in: 'header',
        required: true,
        description: 'Token CSRF requis (session cookie). CSRF id: auth',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['title', 'content', 'price', 'cursusId'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Les outils du jardinier'),
                new OA\Property(property: 'content', type: 'string', example: 'Lorem ipsum...'),
                new OA\Property(property: 'price', type: 'integer', example: 16),
                new OA\Property(property: 'cursusId', type: 'string', example: "Cursus d'initiation au jardinage"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Leçon créée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Les outils du jardinier'),
                new OA\Property(property: 'content', type: 'string', example: 'Lorem ipsum...'),
                new OA\Property(property: 'price', type: 'integer', example: 16),
                new OA\Property(property: 'cursusId', type: 'string', example: "Cursus d'initiation au jardinage"),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Requête invalide (JSON/Champs manquants)',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JSON invalide.'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis) ou CSRF invalide')]
    #[OA\Response(
        response: 422,
        description: 'Validation échouée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation échouée.'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    additionalProperties: true,
                    example: [
                        'title' => ['Le titre ne doit pas être vide.'],
                        'price' => ['Le prix doit être un entier positif.'],
                    ]
                )
            ]
        )
    )]

    /**
     * Create a new lesson
     */
    public function createLesson(
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // CSRF token validation (enabled in prod/dev, bypassed in test)
        if ($this->getParameter('kernel.environment') !== 'test') {
            $csrfValue = (string) $request->headers->get('X-CSRF-TOKEN', '');
            if ($csrfValue === '' || !$csrfTokenManager->isTokenValid(new CsrfToken('auth', $csrfValue))) {
                return $this->json(['message' => 'Token CRSF invalide.'], 403);
            }
        }

        // Decode and validate JSON payload
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        // Hydrate and normalize DTO
        $input = new LessonAdminCreateRequest();
        $input->title = array_key_exists('title', $data) ? (is_string($data['title']) ? (string) $data['title'] : null) : null;
        $input->content = array_key_exists('content', $data) ? (is_string($data['content']) ? (string) $data['content'] : null) : null;
        $input->price = array_key_exists('price', $data) ? (is_numeric($data['price']) ? (int) $data['price'] : null) : null;
        $input->cursusId = array_key_exists('cursusId', $data) ? (is_numeric($data['cursusId']) ? (int) $data['cursusId'] : null) : null;
        $input->normalize();

        // Validate DTO
        $violations = $validator->validate($input);
        $errors = [];
        foreach ($violations as $v) {
            $field = (string) $v->getPropertyPath();
            $errors[$field][] = $v->getMessage();
        }

        if (!empty($errors)) {
            return $this->json(['message' => 'Validation échouée.', 'errors' => $errors], 422);
        }

        // Find cursus by title
        $cursus = $em->getRepository(Cursus::class)->find($input->cursusId);
        if ($cursus === null) {
            return $this->json(['message' => 'Cursus introuvable.'], 422);
        }

        // Create new lesson
        $lesson = new Lesson();
        $lesson->setTitle((string) $input->title);
        $lesson->setContent((string) $input->content);
        $lesson->setPrice((int) $input->price);
        $lesson->setCursus($cursus);
        $lesson->setIsActive(true);
        $lesson->setPosition($this->computeNextPosition($em, $cursus));

        $author = $this->getUser();
        $authorEmail = $author instanceof User ? $author->getEmail() : null;
        $lesson->setCreatedBy($authorEmail);

        $em->persist($lesson);
        $em->flush();

        // Successful creation response
        return $this->json([
            'message' => 'Leçon créée avec succès.',
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'price' => $lesson->getPrice(),
            'content' => $lesson->getContent(),
            'position' => $lesson->getPosition(),
            'isActive' => $lesson->isActive(),
            'createdAt' => $lesson->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $lesson->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'createdBy' => $lesson->getCreatedBy(),
            'updatedBy' => $lesson->getUpdatedBy(),
        ], 201);
    }

    /**
     * Compute the next position within the cursus
     */
    private function computeNextPosition(EntityManagerInterface $em, Cursus $cursus): int
    {
        $qb = $em->createQueryBuilder()
            ->select('COALESCE(MAX(l.position), 0) as maxPos')
            ->from(Lesson::class, 'l')
            ->andWhere('l.cursus = :cursus')
            ->setParameter('cursus', $cursus);

        $maxPos = (int) $qb->getQuery()->getSingleScalarResult();

        return $maxPos + 1;
    }

    // --- UPDATE ---
    // - Update lesson
    #[Route('/api/admin/lessons/{id}', name: 'api_admin_lessons_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Patch(
        path: '/api/admin/lessons/{id}',
        summary: "Mettre à jour une leçon (admin)",
        tags: ['Admin - Lessons']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant de la leçon",
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'X-CSRF-TOKEN',
        in: 'header',
        required: true,
        description: 'Token CSRF requis (session cookie). CSRF id: auth',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Titre mis à jour'),
                new OA\Property(property: 'content', type: 'string', example: 'Contenu mis à jour'),
                new OA\Property(property: 'price', type: 'integer', example: 20),
                new OA\Property(property: 'cursusId', type: 'integer', example: 10, description: 'Interdit via PATCH'),
                new OA\Property(property: 'themeId', type: 'integer', example: 3, description: 'Interdit (dérivé du cursus)'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Leçon mise à jour',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Titre mis à jour'),
                new OA\Property(property: 'content', type: 'string', example: 'Contenu mis à jour'),
                new OA\Property(property: 'price', type: 'integer', example: 20),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Requête invalide (JSON)",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JSON invalide.'),
            ]
        )

    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 403, description: 'Accès interdit (ROLE_ADMIN requis) ou CSRF invalide')]
    #[OA\Response(
        response: 404,
        description: "Leçon introuvable",
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Leçon introuvable.'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation échouée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation échouée.'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    additionalProperties: true,
                    example: ['price' => 'Le prix doit être un entier positif.',]
                )
            ]
        )
    )]

    /**
     * Update an existing lesson
     */
    public function updateLesson(
        int $id,
        Request $request,
        LessonRepository $lessons,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // CSRF token validation (enabled in prod/dev, bypassed in test)
        if ($this->getParameter('kernel.environment') !== 'test') {
            $csrfValue = (string) $request->headers->get('X-CSRF-TOKEN', '');
            if ($csrfValue === '' || !$csrfTokenManager->isTokenValid(new CsrfToken('auth', $csrfValue))) {
                return $this->json(['message' => 'Token CRSF invalide.'], 403);
            }
        }

        // Find existing lesson
        $lesson = $lessons->find($id);
        if ($lesson === null) {
            return $this->json(['message' => 'Leçon introuvable.'], 404);
        }

        // Decode and validate JSON payload
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        // Prevent any modification of cursus/theme and isActive via PATCH.
        if (array_key_exists('cursusId', $data) || array_key_exists('themeId', $data) || array_key_exists('isActive', $data)) {
            return $this->json([
                'message' => 'Champs "cursusId", "themeId" et "isActive" non modifiables via cette route.'
            ], 400);
        }

        // Hydrate and normalize DTO
        $input = new LessonAdminUpdateRequest();
        $input->title = array_key_exists('title', $data) ? (is_string($data['title']) ? (string) $data['title'] : null) : null;
        $input->content = array_key_exists('content', $data) ? (is_string($data['content']) ? (string) $data['content'] : null) : null;
        $input->price = array_key_exists('price', $data) ? (is_numeric($data['price']) ? (int) $data['price'] : null) : null;
        $input->normalize();

        // Validate DTO
        $violations = $validator->validate($input);
        $errors = [];
        foreach ($violations as $v) {
            $field = (string) $v->getPropertyPath();
            $errors[$field][] = $v->getMessage();
        }

        if (!empty($errors)) {
            return $this->json(['message' => 'Validation échouée.', 'errors' => $errors], 422);
        }

        // Update lesson fields
        if ($input->title !== null) {
            $lesson->setTitle($input->title);
        }
        if ($input->content !== null) {
            $lesson->setContent($input->content);
        }
        if ($input->price !== null) {
            $lesson->setPrice($input->price);
        }

        $author = $this->getUser();
        $authorEmail = $author instanceof User ? $author->getEmail() : null;
        $lesson->setUpdatedBy($authorEmail);

        $em->flush();

        // Successful update response
        return $this->json([
            'message' => 'Leçon mise à jour avec succès.',
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'price' => $lesson->getPrice(),
            'content' => $lesson->getContent(),
            'createdAt' => $lesson->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $lesson->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'createdBy' => $lesson->getCreatedBy(),
            'updatedBy' => $lesson->getUpdatedBy(),
        ], 200);
    }

    // --- DELETE ---
    // - Soft delete lesson
    #[Route('/api/admin/lessons/{id}', name: 'api_admin_lessons_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/admin/lessons/{id}',
        summary: "Supprimer une leçon (soft delete)",
        description: "Suppression logique : met isActive=false. Ne supprime pas la leçon de la base.",
        tags: ['Admin - Lessons']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: "Identifiant de la leçon",
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'X-CSRF-TOKEN',
        in: 'header',
        required: true,
        description: "Token CSRF requis (session cookie). CSRF id: auth",
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Leçon désactivée avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Leçon désactivée'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Leçon introuvable",
        content: new OA\JsonContent(
            type: 'object',
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Leçon introuvable.')]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Accès interdit (ROLE_ADMIN requis) ou CSRF invalide",
        content: new OA\JsonContent(
            type: 'object',
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Token CRSF invalide.')]
        )
    )]
    #[OA\Response(
        response: 409,
        description: "Conflit (déjà désactivée)",
        content: new OA\JsonContent(
            type: 'object',
            properties: [new OA\Property(property: 'message', type: 'string', example: 'La leçon est déjà désactivée')]
        )
    )]

    /**
     * Soft delete a lesson by setting isActive to false
     */
    public function deleteLesson(
        int $id,
        Request $request,
        LessonRepository $lessons,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        // CSRF token validation (enabled in prod/dev, bypassed in test)
        if ($this->getParameter('kernel.environment') !== 'test') {
            $csrfValue = (string) $request->headers->get('X-CSRF-TOKEN', '');
            if ($csrfValue === '' || !$csrfTokenManager->isTokenValid(new CsrfToken('auth', $csrfValue))) {
                return $this->json(['message' => 'Token CRSF invalide.'], 403);
            }
        }

        // Find existing lesson
        $lesson = $lessons->find($id);
        if ($lesson === null) {
            return $this->json(['message' => 'Leçon introuvable.'], 404);
        }

        // Soft delete by setting isActive to false
        $author = $this->getUser();
        $authorEmail = $author instanceof User ? $author->getEmail() : null;

        if ($lesson->isActive() === true) {
            $lesson->setIsActive(false);
            $lesson->setUpdatedBy($authorEmail);
            $em->flush();

            return $this->json(['message' => 'Leçon désactivée'], 200);
        }

        // Already inactive
        return $this->json(['message' => 'La leçon est déjà désactivée'], 409);
    }
}

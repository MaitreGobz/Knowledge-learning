<?php

namespace App\Tests\Controller\Api\Public;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the CursusController API endpoints.
 */
final class CursusControllerTest extends WebTestCase
{
    public function testGetCursusReturnsCursusWithLessonsPreview(): void
    {
        $client = static::createClient();

        /**
         * First, call the /api/themes endpoint to get a cursus ID.
         */
        $client->request('GET', '/api/themes');

        self::assertResponseIsSuccessful();

        $themesResponse = $client->getResponse();
        self::assertTrue(
            str_contains((string) $themesResponse->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/themes doit être au format JSON.'
        );

        $themesContent = (string) $themesResponse->getContent();
        self::assertNotSame('', $themesContent);

        $themesData = json_decode($themesContent, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($themesData);
        self::assertNotEmpty($themesData);

        // Looking for the first available cursus within the themes
        $cursusId = null;

        foreach ($themesData as $theme) {
            if (!isset($theme['cursus']) || !is_array($theme['cursus'])) {
                continue;
            }

            if (!empty($theme['cursus']) && isset($theme['cursus'][0]['id'])) {
                $cursusId = $theme['cursus'][0]['id'];
                break;
            }
        }

        self::assertNotNull(
            $cursusId,
            'Aucun cursus n\'a été trouvé via /api/themes.'
        );

        /**
         * Call the /api/cursus/{id} endpoint to get cursus details.
         */
        $client->request('GET', '/api/cursus/' . $cursusId);

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();

        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse doit être au format JSON.'
        );

        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Assertions structure
        self::assertIsArray($data);

        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('title', $data);
        self::assertArrayHasKey('description', $data);
        self::assertArrayHasKey('price', $data);
        self::assertArrayHasKey('lessons', $data);

        self::assertSame($cursusId, $data['id']);

        // Lessons preview
        self::assertIsArray($data['lessons']);

        if (!empty($data['lessons'])) {
            $lesson = $data['lessons'][0];

            self::assertArrayHasKey('id', $lesson);
            self::assertArrayHasKey('title', $lesson);
            self::assertArrayHasKey('price', $lesson);
            self::assertArrayHasKey('position', $lesson);
        }
    }

    public function testGetCursusReturns404WhenNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cursus/999999');

        self::assertResponseStatusCodeSame(404);
    }
}

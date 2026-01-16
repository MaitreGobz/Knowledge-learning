<?php

namespace App\Tests\Controller\Api\Public;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the ThemeController API endpoints.
 */
final class ThemeControllerTest extends WebTestCase
{
    public function testGetThemesReturnsThemesWithCursusPreview(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/themes');

        self::assertResponseIsSuccessful();

        // Get the response
        $response = $client->getResponse();

        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse doit être au format JSON.'
        );

        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Basic assertions on the structure of the response
        self::assertIsArray($data);
        self::assertNotEmpty($data);

        // Check the first theme in the list
        $first = $data[0];

        // Check that the first theme has the expected keys
        self::assertArrayHasKey('id', $first);
        self::assertArrayHasKey('title', $first);
        self::assertArrayHasKey('slug', $first);
        self::assertArrayHasKey('description', $first);
        self::assertArrayHasKey('cursus', $first);

        // Check that cursus is an array and has expected structure
        self::assertIsArray($first['cursus']);
        if (!empty($first['cursus'])) {
            $c = $first['cursus'][0];
            self::assertArrayHasKey('id', $c);
            self::assertArrayHasKey('title', $c);
            self::assertArrayHasKey('description', $c);
            self::assertArrayHasKey('price', $c);
        }
    }
}

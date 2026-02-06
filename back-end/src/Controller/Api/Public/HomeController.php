<?php

namespace App\Controller\Api\Public;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for the home page of the API.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[OA\Get(
        path: "/",
        summary: "Page d'accueil de l'API",
        tags: ["Home"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Réponse réussie avec la page d'accueil de l'API",
            )
        ]
    )]

    /**
     * Renders the home page of the API.
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'appName' => 'Knowledge Learning API',
        ]);
    }
}

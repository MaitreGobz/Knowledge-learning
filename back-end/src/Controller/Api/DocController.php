<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DocController
{
    #[Route('/api/doc', name: 'api_doc', methods: ['GET'])]
    public function __invoke(): RedirectResponse
    {
        return new RedirectResponse('/swagger-ui/index.html');
    }
}

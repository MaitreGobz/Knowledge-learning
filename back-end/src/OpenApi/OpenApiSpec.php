<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Knowledge Learning API",
    version: "1.0.0",
    description: "Documentation OpenAPI générée depuis le code (Symfony 8 / PHP 8.4)",
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
)]
final class OpenApiSpec
{
    
}
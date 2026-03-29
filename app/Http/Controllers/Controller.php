<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '2.1.7',
    title: 'MovieShelf API v2',
    description: 'API Dokumentation für MovieShelf - Dein digitales Filmregal',
    contact: new OA\Contact(email: 'support@neuhaus.ovh'),
    license: new OA\License(name: 'Apache 2.0', url: 'http://www.apache.org/licenses/LICENSE-2.0.html')
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'MovieShelf API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'apiAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: "Geben Sie Ihren Bearer-Token im Format 'Bearer {token}' ein."
)]
abstract class Controller
{
    //
}

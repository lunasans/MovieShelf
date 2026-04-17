<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

class InfoController extends Controller
{
    #[OA\Get(
        path: '/info',
        summary: 'System-Informationen abrufen',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'System-Informationen (Name, Version)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'app_name', type: 'string'),
                        new OA\Property(property: 'version', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        return response()->json([
            'app_name' => config('app.name'),
            'version' => config('app.shelf_version'),
            'saas_version' => config('app.saas_version'),
            'shelf_version' => config('app.shelf_version'),
        ]);
    }
}

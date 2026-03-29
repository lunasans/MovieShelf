<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActorResource;
use App\Models\Actor;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ActorController extends Controller
{
    #[OA\Get(
        path: '/actors',
        summary: 'Schauspielerliste abrufen (paginiert)',
        tags: ['Actors'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Anzahl pro Seite', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Seitenzahl', required: false, schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste der Schauspieler',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Nicht autorisiert')
        ]
    )]
    public function index(Request $request)
    {
        $actors = Actor::orderBy('last_name', 'asc')
            ->paginate($request->get('per_page', 20));

        return ActorResource::collection($actors);
    }

    #[OA\Get(
        path: '/actors/{actor}',
        summary: 'Schauspieler-Details abrufen',
        tags: ['Actors'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'actor', in: 'path', description: 'Schauspieler ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detaillierte Schauspielerinformationen inkl. Filmografie',
                content: new OA\JsonContent()
            ),
            new OA\Response(response: 404, description: 'Schauspieler nicht gefunden')
        ]
    )]
    public function show(Actor $actor)
    {
        $actor->load(['movies']);
        
        return new ActorResource($actor);
    }

    #[OA\Get(
        path: '/actors/search',
        summary: 'Nach Schauspielern suchen',
        tags: ['Actors'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', description: 'Suchbegriff (Vor- oder Nachname)', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Suchergebnisse',
                content: new OA\JsonContent()
            )
        ]
    )]
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $actors = Actor::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->paginate(20);

        return ActorResource::collection($actors);
    }
}

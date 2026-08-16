<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    #[OA\Get(
        path: '/tags',
        summary: 'Alle verfügbaren Tags abrufen',
        tags: ['Tags'],
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste der Tags',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'tag', type: 'string'),
                                    new OA\Property(property: 'count', type: 'integer')
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Nicht autorisiert')
        ]
    )]
    public function index(Request $request)
    {
        $allTagStrings = Movie::where('is_deleted', false)
            ->whereNotNull('tag')
            ->where('tag', '!=', '')
            ->pluck('tag');

        $tagCounts = [];
        foreach ($allTagStrings as $string) {
            $parts = array_map('trim', explode(',', $string));
            foreach ($parts as $tag) {
                if ($tag) {
                    $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                }
            }
        }

        ksort($tagCounts);

        $tags = collect($tagCounts)
            ->map(fn ($count, $name) => ['tag' => $name, 'count' => $count])
            ->values();

        return response()->json(['data' => $tags]);
    }
}

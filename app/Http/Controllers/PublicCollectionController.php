<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicCollectionController extends Controller
{
    public function index(Request $request)
    {
        if (Setting::get('public_collection_enabled', '0') !== '1') {
            abort(404);
        }

        $query = Movie::query()->whereNull('boxset_parent');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', '%'.$q.'%')
                  ->orWhere('genre', 'like', '%'.$q.'%')
                  ->orWhere('director', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%'.$request->genre.'%');
        }

        if ($request->filled('year_from')) {
            $query->where('year', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('year', '<=', (int) $request->year_to);
        }

        if ($request->filled('rating_min')) {
            $query->whereNotNull('rating')->where('rating', '>=', (float) $request->rating_min);
        }

        if ($request->filled('runtime_max')) {
            $query->whereNotNull('runtime')->where('runtime', '<=', (int) $request->runtime_max);
        }

        $perPage = Setting::get('items_per_page', 20);
        $movies = $query->withCount('boxsetChildren')->orderBy('title')->paginate($perPage)->withQueryString();

        $collectionTypes = Movie::distinct()->whereNotNull('collection_type')->orderBy('collection_type')->pluck('collection_type');

        $genres = Movie::whereNotNull('genre')->pluck('genre')
            ->flatMap(fn ($g) => array_map('trim', explode(',', $g)))
            ->filter()->unique()->sort()->values();

        $siteTitle = Setting::get('site_title', 'MovieShelf');

        return view('tenant.collection.index', compact('movies', 'collectionTypes', 'genres', 'siteTitle'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Actor::query();
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("last_name || ' ' || first_name LIKE ?", ["%{$search}%"]);
            });
        }
        $actors = $query->withCount('movies')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return view('admin.actors.index', compact('actors'));
    }

    /**
     * Search actors for JSON response (Manual assignment).
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $actors = Actor::where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"])
                ->orWhereRaw("last_name || ' ' || first_name LIKE ?", ["%{$search}%"]);
        })
        ->limit(10)
        ->get();

        // profile_url (server-seitig aufgelöst, S3/medien-fähig) für die Bild-Vorschau mitgeben
        $actors->each->append('profile_url');

        return response()->json($actors);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.actors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ]);
        // Einwort-/CJK-Namen haben keinen Nachnamen; Spalte ist NOT NULL.
        $validated['last_name'] = $validated['last_name'] ?? '';

        // Check for duplicates
        $exists = Actor::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['first_name' => 'Ein Schauspieler mit diesem Namen existiert bereits.']);
        }

        $validated['slug'] = Str::slug(trim($validated['first_name'].' '.$validated['last_name']));
        $actor = Actor::create($validated);

        return redirect()->route('admin.actors.index')->with('success', $this->formatSuccessMessage($actor, 'angelegt'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actor $actor)
    {
        return view('admin.actors.edit', compact('actor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Actor $actor)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ]);
        $validated['last_name'] = $validated['last_name'] ?? '';

        // Check for duplicates (excluding current actor)
        $exists = Actor::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->where('id', '!=', $actor->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['first_name' => 'Ein anderer Schauspieler mit diesem Namen existiert bereits.']);
        }

        $validated['slug'] = Str::slug(trim($validated['first_name'].' '.$validated['last_name']));

        // Manuell geänderte Bio markieren, damit der ActorBot sie nicht überschreibt.
        if (($validated['bio'] ?? '') !== ($actor->bio ?? '')) {
            $validated['bio_locale'] = null;
        }
        $actor->update($validated);

        return redirect()->route('admin.actors.index')->with('success', $this->formatSuccessMessage($actor, 'aktualisiert'));
    }

    /**
     * Translate a biography text via LibreTranslate (AJAX).
     */
    public function translateBio(Request $request, TranslationService $translator)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:20000',
        ]);

        $result = $translator->translate($validated['text']);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Actor $actor)
    {
        $message = $this->formatSuccessMessage($actor, 'gelöscht');
        $actor->delete();

        return redirect()->route('admin.actors.index')->with('success', $message);
    }

    /**
     * Format a success message for actor actions.
     */
    private function formatSuccessMessage(Actor $actor, string $action): string
    {
        return sprintf('Schauspieler "%s %s" wurde %s.', $actor->first_name, $actor->last_name, $action);
    }
}
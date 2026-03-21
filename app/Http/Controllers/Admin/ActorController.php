<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
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
                    ->orWhere('nationality', 'like', '%'.$search.'%');
            });
        }
        $actors = $query->withCount('movies')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return view('admin.actors.index', compact('actors'));
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
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
        ]);
        $validated['slug'] = Str::slug($validated['first_name'].' '.$validated['last_name']);
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
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
        ]);
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $validated['slug'] = Str::slug($request->first_name.' '.$request->last_name);
        }
        $actor->update($validated);

        return redirect()->route('admin.actors.index')->with('success', $this->formatSuccessMessage($actor, 'aktualisiert'));
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
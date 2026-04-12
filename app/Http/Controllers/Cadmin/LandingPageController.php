<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPage::orderBy('sort_order')->orderBy('title')->get();
        return view('cadmin.landing.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('cadmin.landing.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:landing_pages,slug',
            'content'     => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
        ]);

        $data['slug']        = $data['slug'] ?: LandingPage::generateSlug($data['title']);
        $data['is_active']   = $request->boolean('is_active');
        $data['show_in_nav'] = $request->boolean('show_in_nav');

        LandingPage::create($data);

        return redirect()->route('cadmin.landing.pages.index')->with('success', 'Seite erfolgreich erstellt.');
    }

    public function edit(LandingPage $page)
    {
        return view('cadmin.landing.pages.edit', compact('page'));
    }

    public function update(Request $request, LandingPage $page)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:landing_pages,slug,' . $page->id,
            'content'     => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
        ]);

        $data['slug']        = $data['slug'] ?: LandingPage::generateSlug($data['title']);
        $data['is_active']   = $request->boolean('is_active');
        $data['show_in_nav'] = $request->boolean('show_in_nav');

        $page->update($data);

        return redirect()->route('cadmin.landing.pages.index')->with('success', 'Seite erfolgreich aktualisiert.');
    }

    public function destroy(LandingPage $page)
    {
        $page->delete();
        return redirect()->route('cadmin.landing.pages.index')->with('success', 'Seite gelöscht.');
    }
}

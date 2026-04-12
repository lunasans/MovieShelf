<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class CentralFaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->get();
        return view('cadmin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('cadmin.faqs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        Faq::create($data);

        return redirect()->route('cadmin.faqs.index')->with('success', 'FAQ wurde erfolgreich erstellt.');
    }

    public function edit(Faq $faq)
    {
        return view('cadmin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $faq->update($data);

        return redirect()->route('cadmin.faqs.index')->with('success', 'FAQ wurde erfolgreich aktualisiert.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('cadmin.faqs.index')->with('success', 'FAQ wurde erfolgreich gelöscht.');
    }
}

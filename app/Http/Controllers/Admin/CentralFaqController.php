<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class CentralFaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->get();
        return view('admin.central_faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.central_faqs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ wurde erfolgreich erstellt.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.central_faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq->update($data);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ wurde erfolgreich aktualisiert.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ wurde erfolgreich gelöscht.');
    }
}

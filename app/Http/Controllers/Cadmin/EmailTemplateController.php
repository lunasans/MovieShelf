<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->get();
        return view('cadmin.email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $template)
    {
        return view('cadmin.email-templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template->update($data);

        return redirect()->route('cadmin.email-templates.index')
            ->with('success', "E-Mail Template '{$template->name}' wurde aktualisiert.");
    }
}

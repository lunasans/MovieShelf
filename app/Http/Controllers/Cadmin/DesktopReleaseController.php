<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\DesktopRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesktopReleaseController extends Controller
{
    public function index()
    {
        $releases = DesktopRelease::orderBy('created_at', 'desc')->get();
        return view('cadmin.desktop.index', compact('releases'));
    }

    public function create()
    {
        return view('cadmin.desktop.form', ['release' => new DesktopRelease()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'version'      => 'required|string|unique:desktop_releases,version',
            'changelog'    => 'nullable|string',
            'download_url' => 'nullable|url',
            'exe_file'     => 'nullable|file|max:102400', // max 100MB
            'is_public'    => 'nullable|boolean',
        ]);

        $data = $request->only(['version', 'changelog', 'download_url']);
        $data['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('exe_file')) {
            $file = $request->file('exe_file');
            $filename = 'MovieShelf_v' . $request->version . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('releases', $filename, 'public');
            $data['file_path'] = $path;
            
            // Falls keine externe URL angegeben wurde, generieren wir die interne
            if (!$data['download_url']) {
                $data['download_url'] = Storage::disk('public')->url($path);
            }
        }

        DesktopRelease::create($data);

        return redirect()->route('cadmin.desktop.index')->with('success', 'Release wurde erfolgreich angelegt.');
    }

    public function edit(DesktopRelease $release)
    {
        return view('cadmin.desktop.form', compact('release'));
    }

    public function update(Request $request, DesktopRelease $release)
    {
        $request->validate([
            'version'      => 'required|string|unique:desktop_releases,version,' . $release->id,
            'changelog'    => 'nullable|string',
            'download_url' => 'nullable|url',
            'is_public'    => 'nullable|boolean',
        ]);

        $data = $request->only(['version', 'changelog', 'download_url']);
        $data['is_public'] = $request->boolean('is_public');

        $release->update($data);

        return redirect()->route('cadmin.desktop.index')->with('success', 'Release wurde aktualisiert.');
    }

    public function destroy(DesktopRelease $release)
    {
        if ($release->file_path) {
            Storage::disk('public')->delete($release->file_path);
        }
        $release->delete();
        return back()->with('success', 'Release wurde gelöscht.');
    }
}

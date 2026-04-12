<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\LandingScreenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingScreenshotController extends Controller
{
    public function index()
    {
        $screenshots = LandingScreenshot::orderBy('sort_order')->get();
        return view('cadmin.landing.screenshots', compact('screenshots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images'         => 'required|array|min:1',
            'images.*'       => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'alt_texts'      => 'nullable|array',
            'alt_texts.*'    => 'nullable|string|max:255',
        ]);

        $maxOrder = LandingScreenshot::max('sort_order') ?? -1;

        foreach ($request->file('images') as $i => $file) {
            $filename = 'screenshot_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('screenshots', $filename, 'public');

            LandingScreenshot::create([
                'filename'   => $filename,
                'alt_text'   => $request->input("alt_texts.$i", ''),
                'sort_order' => ++$maxOrder,
                'is_active'  => true,
            ]);
        }

        return back()->with('success', count($request->file('images')) . ' Screenshot(s) erfolgreich hochgeladen.');
    }

    public function update(Request $request, LandingScreenshot $screenshot)
    {
        $request->validate([
            'alt_text'  => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $screenshot->update([
            'alt_text'  => $request->input('alt_text', ''),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Screenshot aktualisiert.');
    }

    public function destroy(LandingScreenshot $screenshot)
    {
        Storage::disk('public')->delete('screenshots/' . $screenshot->filename);
        $screenshot->delete();
        return back()->with('success', 'Screenshot gelöscht.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->order as $position => $id) {
            LandingScreenshot::where('id', $id)->update(['sort_order' => $position]);
        }
        return response()->json(['success' => true]);
    }
}

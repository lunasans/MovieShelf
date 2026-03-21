<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use SimpleXMLElement;
use Exception;

class XmlImportController extends Controller
{
    public function index()
    {
        $files = Storage::disk('local')->files('admin/xml');
        $xmlFiles = array_map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk('local')->size($file),
                'modified' => Storage::disk('local')->lastModified($file),
            ];
        }, array_filter($files, fn($f) => Str::endsWith($f, '.xml')));

        usort($xmlFiles, fn($a, $b) => $b['modified'] <=> $a['modified']);

        return view('admin.import.index', compact('xmlFiles'));
    }

    public function import(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('XML Import gestartet');
        
        $request->validate([
            'xml_file' => 'required|file|max:51200',
        ]);

        $file = $request->file('xml_file');
        \Illuminate\Support\Facades\Log::info('Datei empfangen: ' . $file->getClientOriginalName() . ' (' . $file->getMimeType() . ')');
        
        $xmlContent = '';

        if ($file->getClientOriginalExtension() === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    if (Str::endsWith(strtolower($entry), '.xml')) {
                        $xmlContent = $zip->getFromName($entry);
                        break;
                    }
                }
                $zip->close();
            }
        } else {
            $xmlContent = file_get_contents($file->getRealPath());
        }

        if (empty($xmlContent)) {
            \Illuminate\Support\Facades\Log::warning('Import abgebrochen: Keine XML-Daten gefunden');
            return back()->with('error', 'Keine XML-Daten in der Datei gefunden.');
        }

        try {
            return $this->processXml($xmlContent);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Exception: ' . $e->getMessage());
            return back()->with('error', 'Fehler beim XML-Parsing: ' . $e->getMessage());
        }
    }

    protected function processXml(string $xmlContent)
    {
        libxml_use_internal_errors(true);
        // Disable external entities for security
        if (function_exists('libxml_set_external_entity_loader')) {
            libxml_set_external_entity_loader(null);
        }
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);

        if ($xml === false) {
            $errors = libxml_get_errors();
            $msg = count($errors) > 0 ? $errors[0]->message : 'Unbekannter Fehler';
            return back()->with('error', 'Ungültige XML-Struktur: ' . $msg);
        }

        // DVDProfiler XML typically has DVD elements directly under Collection
        // Use xpath for better detection across different XML structures
        $dvdElements = $xml->xpath('//DVD');
        
        if (empty($dvdElements)) {
            // Fallback for direct children
            $dvdElements = $xml->DVD;
        }

        if (empty($dvdElements) || count($dvdElements) === 0) {
            \Illuminate\Support\Facades\Log::warning('Import abgebrochen: Keine DVD-Elemente gefunden.');
            return back()->with('error', 'Keine DVD-Elemente in der XML gefunden.');
        }

        \Illuminate\Support\Facades\Log::info('Anzahl DVD-Elemente: ' . count($dvdElements));

        // Save XML for history
        $path = 'admin/xml/import_' . date('Ymd_His') . '.xml';
        Storage::disk('local')->put($path, $xmlContent);

        $imported = 0;
        $updated = 0;
        $actorLinks = 0;

        DB::beginTransaction();
        try {
            // Soft delete all
            $deletedCount = Movie::where('is_deleted', false)->update(['is_deleted' => true]);
            \Illuminate\Support\Facades\Log::info($deletedCount . ' Filme als gelöscht markiert.');

            // Phase 1: Mapping
            $idMapping = [];
            $parentChildRelations = [];

            foreach ($dvdElements as $dvd) {
                $collNum = (int)$dvd->CollectionNumber;
                $origId = trim((string)$dvd->ID);
                if ($collNum > 0 && !empty($origId)) $idMapping[$origId] = $collNum;

                if (isset($dvd->BoxSet->Contents->Content)) {
                    $children = [];
                    foreach ($dvd->BoxSet->Contents->Content as $content) {
                        $children[] = trim((string)$content);
                    }
                    if (!empty($children)) $parentChildRelations[$origId] = $children;
                }
            }

            // Phase 2: Import Movies (without parent link first)
            foreach ($dvdElements as $dvd) {
                $id = (int)$dvd->CollectionNumber;
                if ($id <= 0) continue;

                $title = trim((string)$dvd->Title);
                $originalId = trim((string)$dvd->ID);

                $data = [
                    'id' => $id,
                    'title' => $title,
                    'year' => (int)$dvd->ProductionYear ?: null,
                    'genre' => trim((string)($dvd->Genres->Genre[0] ?? '')),
                    'runtime' => (int)$dvd->RunningTime ?: null,
                    'rating_age' => is_numeric((string)$dvd->RatingAge) ? (int)$dvd->RatingAge : null,
                    'overview' => trim((string)($dvd->Overview ?? '')),
                    'cover_id' => $originalId,
                    'backdrop_id' => null,
                    'collection_type' => trim((string)($dvd->CollectionType ?? '')),
                    'boxset_parent' => null, // Set in phase 3
                    'user_id' => auth()->id(),
                    'is_deleted' => false,
                ];

                // Check if exists to determine if it's an update
                $exists = Movie::where('id', $id)->exists();
                $movie = Movie::updateOrCreate(['id' => $id], $data);
                
                if (!$exists) $imported++; else $updated++;

                // Actors
                if (isset($dvd->Actors->Actor)) {
                    $movie->actors()->detach();
                    foreach ($dvd->Actors->Actor as $actorXml) {
                        $firstName = trim((string)($actorXml->FirstName ?? ''));
                        $lastName = trim((string)($actorXml->LastName ?? ''));
                        $role = trim((string)($actorXml->Role ?? ''));

                        if ($firstName || $lastName) {
                            $actor = Actor::firstOrCreate(
                                ['first_name' => $firstName, 'last_name' => $lastName],
                                ['tmdb_id' => null]
                            );

                            $movie->actors()->attach($actor->id, [
                                'role' => $role,
                                'is_main_role' => false,
                                'sort_order' => 0
                            ]);
                            $actorLinks++;
                        }
                    }
                }
            }

            // Phase 3: Update Boxset Relations
            $linkCount = 0;
            foreach ($parentChildRelations as $pOrigId => $childrenOrigIds) {
                $parentId = $idMapping[$pOrigId] ?? null;
                if ($parentId) {
                    foreach ($childrenOrigIds as $cOrigId) {
                        $childId = $idMapping[$cOrigId] ?? null;
                        if ($childId) {
                            Movie::where('id', $childId)->update(['boxset_parent' => $parentId]);
                            $linkCount++;
                        }
                    }
                }
            }
            \Illuminate\Support\Facades\Log::info($linkCount . ' BoxSet-Verknüpfungen aktualisiert.');

            DB::commit();
            \Illuminate\Support\Facades\Log::info("Import fertig: $imported neu, $updated aktualisiert.");
            return back()->with('success', "Import abgeschlossen: $imported neu, $updated aktualisiert. $linkCount BoxSets verknüpft.");

        } catch (Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('ProcessXml Loop Error: ' . $e->getMessage());
            return back()->with('error', 'Import fehlgeschlagen: ' . $e->getMessage() . ' in Zeile ' . $e->getLine());
        }
    }

    public function destroy($filename)
    {
        $path = 'admin/xml/' . $filename;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return back()->with('success', 'Datei gelöscht.');
        }
        return back()->with('error', 'Datei nicht gefunden.');
    }
}

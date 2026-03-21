<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Movie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

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
        }, array_filter($files, fn ($f) => Str::endsWith($f, '.xml')));

        usort($xmlFiles, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return view('admin.import.index', compact('xmlFiles'));
    }

    public function import(Request $request)
    {
        Log::info('XML Import gestartet');
        $request->validate([
            'xml_file' => 'required|file|max:51200',
        ]);

        $file = $request->file('xml_file');
        Log::info('Datei empfangen: '.$file->getClientOriginalName());

        $xmlContent = $this->getXmlContentFromRequest($file);

        if (empty($xmlContent)) {
            Log::warning('Import abgebrochen: Keine XML-Daten gefunden');

            return back()->with('error', 'Keine XML-Daten in der Datei gefunden.');
        }

        try {
            return $this->processXml($xmlContent);
        } catch (Exception $e) {
            Log::error('Import Exception: '.$e->getMessage());

            return back()->with('error', 'Fehler beim XML-Parsing: '.$e->getMessage());
        }
    }

    private function getXmlContentFromRequest($file): string
    {
        $maxSize = 104857600; // 100 MB limit

        if ($file->getClientOriginalExtension() === 'zip') {
            return $this->extractXmlFromZip($file, $maxSize);
        }

        // Limit file_get_contents to prevent memory exhaustion
        $content = file_get_contents($file->getRealPath(), false, null, 0, $maxSize);
        return $content !== false ? $content : '';
    }

    private function extractXmlFromZip($file, int $maxSize): string
    {
        $zip = new ZipArchive;
        $xmlContent = '';

        if ($zip->open($file->getRealPath()) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (Str::endsWith(strtolower($entry), '.xml')) {
                    $stat = $zip->statIndex($i);
                    if ($stat['size'] > $maxSize) {
                        $zip->close();
                        throw new \RuntimeException('Die extrahierte XML-Datei ist zu gross (max. 100 MB).');
                    }
                    // Limit extraction to max size
                    $xmlContent = $zip->getFromName($entry, $maxSize);
                    break;
                }
            }
            $zip->close();
        }

        return $xmlContent;
    }

    protected function processXml(string $xmlContent)
    {
        $xml = $this->loadXml($xmlContent);
        if ($xml instanceof \Illuminate\Http\RedirectResponse) {
            return $xml;
        }

        $dvdElements = $this->extractDvdElements($xml);
        if (empty($dvdElements)) {
            Log::warning('Import abgebrochen: Keine DVD-Elemente gefunden.');

            return back()->with('error', 'Keine DVD-Elemente in der XML gefunden.');
        }

        // Save XML for history
        $path = 'admin/xml/import_'.date('Ymd_His').'.xml';
        Storage::disk('local')->put($path, $xmlContent);

        return $this->performImport($dvdElements);
    }

    private function loadXml(string $xmlContent)
    {
        libxml_use_internal_errors(true);
        if (function_exists('libxml_set_external_entity_loader')) {
            libxml_set_external_entity_loader(null);
        }

        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        if ($xml === false) {
            $errors = libxml_get_errors();
            $msg = ! empty($errors) ? $errors[0]->message : 'Unbekannter Fehler';

            return back()->with('error', 'Ungültige XML-Struktur: '.$msg);
        }

        return $xml;
    }

    private function extractDvdElements($xml): array
    {
        $dvdElements = $xml->xpath('//DVD');

        return $dvdElements ?: (array) $xml->DVD;
    }

    private function performImport(array $dvdElements)
    {
        $stats = ['imported' => 0, 'updated' => 0];

        DB::beginTransaction();
        try {
            // Soft delete all active movies
            Movie::where('is_deleted', false)->update(['is_deleted' => true]);

            // Phase 1: Mapping
            [$idMapping, $parentChildRelations] = $this->buildMappings($dvdElements);

            // Phase 2: Import Movies
            foreach ($dvdElements as $dvd) {
                $result = $this->importMovie($dvd);
                if ($result === 'imported') {
                    $stats['imported']++;
                } elseif ($result === 'updated') {
                    $stats['updated']++;
                }
            }

            // Phase 3: Relations
            $linkCount = $this->linkBoxsets($idMapping, $parentChildRelations);

            DB::commit();
            Log::info("Import fertig: {$stats['imported']} neu, {$stats['updated']} aktualisiert.");

            return back()->with('success', "Import abgeschlossen: {$stats['imported']} neu, {$stats['updated']} aktualisiert. $linkCount BoxSets verknüpft.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Import Loop Error: '.$e->getMessage());

            return back()->with('error', 'Import fehlgeschlagen: '.$e->getMessage());
        }
    }

    private function buildMappings(array $dvdElements): array
    {
        $idMapping = [];
        $relations = [];

        foreach ($dvdElements as $dvd) {
            $collNum = (int) $dvd->CollectionNumber;
            $origId = trim((string) $dvd->ID);

            if ($collNum > 0 && ! empty($origId)) {
                $idMapping[$origId] = $collNum;
            }

            if (isset($dvd->BoxSet->Contents->Content)) {
                $children = [];
                foreach ($dvd->BoxSet->Contents->Content as $content) {
                    $children[] = trim((string) $content);
                }
                if (! empty($children)) {
                    $relations[$origId] = $children;
                }
            }
        }

        return [$idMapping, $relations];
    }

    private function importMovie($dvd): string
    {
        $id = (int) $dvd->CollectionNumber;
        if ($id <= 0) {
            return 'skipped';
        }

        $originalId = trim((string) $dvd->ID);
        $exists = Movie::where('id', $id)->exists();

        Movie::updateOrCreate(['id' => $id], [
            'title' => trim((string) $dvd->Title),
            'year' => (int) $dvd->ProductionYear ?: null,
            'genre' => trim((string) ($dvd->Genres->Genre[0] ?? '')),
            'runtime' => (int) $dvd->RunningTime ?: null,
            'rating_age' => is_numeric((string) $dvd->RatingAge) ? (int) $dvd->RatingAge : null,
            'overview' => trim((string) ($dvd->Overview ?? '')),
            'cover_id' => $originalId,
            'collection_type' => trim((string) ($dvd->CollectionType ?? '')),
            'user_id' => auth()->id(),
            'is_deleted' => false,
        ]);

        $movie = Movie::find($id);
        $this->syncActors($movie, $dvd);

        return $exists ? 'updated' : 'imported';
    }

    private function syncActors(Movie $movie, $dvd)
    {
        if (! isset($dvd->Actors->Actor)) {
            return;
        }

        $movie->actors()->detach();
        foreach ($dvd->Actors->Actor as $actorXml) {
            $firstName = trim((string) ($actorXml->FirstName ?? ''));
            $lastName = trim((string) ($actorXml->LastName ?? ''));

            if ($firstName || $lastName) {
                $actor = Actor::firstOrCreate(
                    ['first_name' => $firstName, 'last_name' => $lastName],
                    ['tmdb_id' => null]
                );
                $movie->actors()->attach($actor->id, [
                    'role' => trim((string) ($actorXml->Role ?? '')),
                    'is_main_role' => false,
                    'sort_order' => 0,
                ]);
            }
        }
    }

    private function linkBoxsets(array $idMapping, array $relations): int
    {
        $count = 0;
        foreach ($relations as $pOrigId => $children) {
            $parentId = $idMapping[$pOrigId] ?? null;
            if (! $parentId) {
                continue;
            }

            foreach ($children as $cOrigId) {
                $childId = $idMapping[$cOrigId] ?? null;
                if ($childId) {
                    Movie::where('id', $childId)->update(['boxset_parent' => $parentId]);
                    $count++;
                }
            }
        }

        return $count;
    }

    public function destroy($filename)
    {
        $path = 'admin/xml/'.$filename;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);

            return back()->with('success', 'Datei gelöscht.');
        }

        return back()->with('error', 'Datei nicht gefunden.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MigrationController extends Controller
{
    public function index()
    {
        if (\App\Models\Setting::get('migration_enabled', '1') == '0') {
            abort(404);
        }

        $connectionStatus = false;
        $error = null;

        try {
            DB::connection('mysql_v1')->getPdo();
            $connectionStatus = true;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('admin.migration.index', compact('connectionStatus', 'error'));
    }

    public function run(Request $request, MigrationService $migrationService)
    {
        if (\App\Models\Setting::get('migration_enabled', '1') == '0') {
            abort(404);
        }

        $logs = [];
        $modules = $request->get('modules', []);
        $movieFields = $request->get('movie_fields', []);
        
        try {
            $migrationService->migrate(
                $request->has('fresh'), 
                $modules, 
                $movieFields, 
                $request->get('v1_path'),
                function ($message) use (&$logs) {
                    $logs[] = $message;
                }
            );

            return back()->with('success', 'Migration erfolgreich abgeschlossen!')->with('migration_logs', $logs);
        } catch (\Exception $e) {
            return back()->with('error', 'Migration fehlgeschlagen: ' . $e->getMessage())->with('migration_logs', $logs);
        }
    }
}

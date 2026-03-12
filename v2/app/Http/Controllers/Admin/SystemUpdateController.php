<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SystemUpdateController extends Controller
{
    public function index()
    {
        try {
            $currentCommit = $this->runCommand('git rev-parse --short HEAD');
            $currentBranch = $this->runCommand('git rev-parse --abbrev-ref HEAD');
            $lastChanges = explode("\n", $this->runCommand('git log -n 5 --pretty=format:"%h|%s|%cr"'));
            
            $formattedChanges = [];
            foreach ($lastChanges as $change) {
                if (empty($change)) continue;
                $parts = explode('|', $change);
                $formattedChanges[] = [
                    'hash' => $parts[0] ?? '???',
                    'msg' => $parts[1] ?? '???',
                    'date' => $parts[2] ?? '???'
                ];
            }

            // Check if update is needed (briefly)
            $local = $this->runCommand('git rev-parse @');
            $remote = $this->runCommand('git rev-parse @{u}');
            $needsUpdate = ($local !== $remote);

            return view('admin.update.index', compact('currentCommit', 'currentBranch', 'formattedChanges', 'needsUpdate'));
        } catch (\Exception $e) {
            Log::error('Update Check failed: ' . $e->getMessage());
            return view('admin.update.index', [
                'currentCommit' => '???',
                'currentBranch' => '???',
                'formattedChanges' => [],
                'needsUpdate' => false,
                'error' => 'Fehler beim Abrufen der Versionsinformationen: ' . $e->getMessage()
            ]);
        }
    }

    public function check()
    {
        try {
            $this->runCommand('git fetch');
            return redirect()->route('admin.update.index')->with('success', 'Updates wurden geprüft.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update-Check fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function update()
    {
        try {
            Log::info('System Update started...');
            $pull = $this->runCommand('git pull');
            Log::info('Git Pull Output: ' . $pull);
            
            $migrate = $this->runCommand('php artisan migrate --force');
            Log::info('Migration Output: ' . $migrate);
            
            return redirect()->route('admin.update.index')->with('success', 'System erfolgreich aktualisiert.');
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return back()->with('error', 'Update fehlgeschlagen: ' . $e->getMessage());
        }
    }

    private function runCommand($cmd)
    {
        $gitBinary = config('app.git_binary', 'git');
        
        // Prüfe, ob der konfigurierte Pfad existiert, sonst Fallback auf 'git'
        if ($gitBinary !== 'git' && !file_exists($gitBinary)) {
            $gitBinary = 'git';
        }

        // Ersetze 'git ' am Anfang des Befehls durch den (evtl. gequoteten) Pfad
        if (str_starts_with($cmd, 'git ')) {
            $cmd = '"' . $gitBinary . '"' . substr($cmd, 3);
        }

        $result = Process::path(base_path())->run($cmd);
        
        if (!$result->successful()) {
            $error = $result->errorOutput() ?: $result->output();
            Log::warning("Git command failed ($cmd): " . $error);
            throw new \Exception($error);
        }
        
        return trim($result->output());
    }
}

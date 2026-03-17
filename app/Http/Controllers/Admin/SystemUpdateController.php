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
                'currentCommit' => 'v' . config('app.version'),
                'currentBranch' => 'Release',
                'formattedChanges' => [],
                'needsUpdate' => false,
                'error' => 'Git-Informationen konnten nicht geladen werden: ' . $e->getMessage()
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
            
            // Check for local changes
            $status = $this->runCommand('git status --porcelain');
            $hasChanges = !empty($status);
            $stashApplied = false;

            if ($hasChanges) {
                Log::info('Local changes detected. Stashing...');
                $this->runCommand('git stash');
                $stashApplied = true;
            }
            
            $pull = $this->runCommand('git pull');
            Log::info('Git Pull Output: ' . $pull);
            
            if ($stashApplied) {
                try {
                    Log::info('Applying local changes back...');
                    $this->runCommand('git stash pop');
                } catch (\Exception $e) {
                    Log::warning('Conflict while applying local changes: ' . $e->getMessage());
                    // We don't fail the whole update here, but we log the conflict
                }
            }
            
            $migrate = $this->runCommand('php artisan migrate --force');
            Log::info('Migration Output: ' . $migrate);
            
            $configClear = $this->runCommand('php artisan config:clear');
            Log::info('Config Clear Output: ' . $configClear);
            
            try {
                $npmInstall = $this->runCommand('npm install');
                Log::info('NPM Install Output: ' . $npmInstall);
                
                $npmBuild = $this->runCommand('npm run build');
                Log::info('NPM Build Output: ' . $npmBuild);
            } catch (\Exception $e) {
                Log::error('NPM Build failed: ' . $e->getMessage());
                return redirect()->route('admin.update.index')->with('warning', 'System aktualisiert, aber Frontend-Build (npm) schlug fehl: ' . $e->getMessage());
            }
            
            return redirect()->route('admin.update.index')->with('success', 'System erfolgreich aktualisiert. Lokale Anpassungen wurden beibehalten.');
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return back()->with('error', 'Update fehlgeschlagen: ' . $e->getMessage());
        }
    }

    private function runCommand($cmd)
    {
        $gitBinary = config('app.git_binary', 'git');
        
        // On Linux, if it's a Windows path, it won't exist. Fallback to 'git'.
        if ($gitBinary !== 'git' && !file_exists($gitBinary)) {
            Log::debug("Configured git binary not found: $gitBinary. Falling back to 'git'.");
            $gitBinary = 'git';
        }

        // Replace 'git ' prefix with the actual binary path
        if (str_starts_with($cmd, 'git ')) {
            $executable = PHP_OS_FAMILY === 'Windows' ? '"' . $gitBinary . '"' : $gitBinary;
            $cmd = $executable . substr($cmd, 3);
        }

        Log::debug("Executing command: $cmd in " . base_path());
        
        $result = Process::path(base_path())->run($cmd);
        
        if (!$result->successful()) {
            $error = $result->errorOutput() ?: $result->output();
            $exitCode = $result->exitCode();

            // Auto-fix for "dubious ownership"
            if ($exitCode === 128 && str_contains($error, 'safe.directory')) {
                Log::info("Detected dubious ownership. Attempting auto-fix...");
                $fixCmd = $executable . ' config --global --add safe.directory ' . str_replace('\\', '/', base_path());
                Process::path(base_path())->run($fixCmd);
                
                // Try original command again
                $result = Process::path(base_path())->run($cmd);
                if ($result->successful()) {
                    return trim($result->output());
                }
            }

            Log::warning("Git command failed (Exit $exitCode): $cmd. Error: " . $error);
            throw new \Exception("Git error ($exitCode): " . $error);
        }
        
        return trim($result->output());
    }
}

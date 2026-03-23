<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

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
                if (empty($change)) {
                    continue;
                }
                $parts = explode('|', $change);
                $formattedChanges[] = [
                    'hash' => $parts[0] ?? '???',
                    'msg' => $parts[1] ?? '???',
                    'date' => $parts[2] ?? '???',
                ];
            }

            // Check if update is needed (briefly)
            $local = $this->runCommand('git rev-parse @');
            $remote = $this->runCommand('git rev-parse @{u}');
            $needsUpdate = ($local !== $remote);

            $ignoredUpdateFiles = \App\Models\Setting::get('ignored_update_files', '');

            return view('admin.update.index', compact('currentCommit', 'currentBranch', 'formattedChanges', 'needsUpdate', 'ignoredUpdateFiles'));
        } catch (\Exception $e) {
            Log::error('Update Check failed: '.$e->getMessage());

            return view('admin.update.index', [
                'currentCommit' => 'v'.config('app.version'),
                'currentBranch' => 'Release',
                'formattedChanges' => [],
                'needsUpdate' => false,
                'error' => 'Git-Informationen konnten nicht geladen werden: '.$e->getMessage(),
            ]);
        }
    }

    public function check()
    {
        try {
            $this->runCommand('git fetch');

            return redirect()->route('admin.update.index')->with('success', 'Updates wurden geprüft.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update-Check fehlgeschlagen: '.$e->getMessage());
        }
    }

    public function update()
    {
        try {
            Log::info('System Update started...');

            // Auto-clean stale locks before starting
            $lockFile = base_path('.git/index.lock');
            if (file_exists($lockFile)) {
                Log::info('Stale git lock found. Removing...');
                unlink($lockFile);
            }

            // Get ignored files to protect during update
            $ignoredFilesRaw = \App\Models\Setting::get('ignored_update_files', '');
            $ignoredFiles = array_filter(array_map('trim', explode("\n", $ignoredFilesRaw)));
            $backupDir = storage_path('app/temp/update_backups_'.time());
            $protectedFiles = [];

            if (! empty($ignoredFiles)) {
                if (! is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }

                foreach ($ignoredFiles as $file) {
                    $fullPath = base_path($file);
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        $backupPath = $backupDir.'/'.str_replace(['/', '\\'], '_', $file);
                        copy($fullPath, $backupPath);
                        $protectedFiles[$file] = $backupPath;
                        Log::info("Protected file backed up: $file");
                    }
                }
            }

            // Check for local changes
            $status = $this->runCommand('git status --porcelain');
            $hasChanges = ! empty($status);
            $stashApplied = false;

            if ($hasChanges) {
                Log::info('Local changes detected. Stashing...');
                $this->runCommand('git stash');
                $stashApplied = true;
            }

            $pull = $this->runCommand('git pull');
            Log::info('Git Pull Output: '.$pull);

            if ($stashApplied) {
                try {
                    Log::info('Applying local changes back...');
                    $this->runCommand('git stash pop');
                } catch (\Exception $e) {
                    Log::warning('Conflict while applying local changes: '.$e->getMessage());
                    // We don't fail the whole update here, but we log the conflict
                }
            }

            // Restore protected files
            foreach ($protectedFiles as $file => $backupPath) {
                if (file_exists($backupPath)) {
                    $fullPath = base_path($file);
                    $dir = dirname($fullPath);
                    if (! is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy($backupPath, $fullPath);
                    Log::info("Protected file restored: $file");
                    unlink($backupPath);
                }
            }
            if (isset($backupDir) && is_dir($backupDir)) {
                @rmdir($backupDir);
            }

            $migrate = $this->runCommand('php artisan migrate --force');
            Log::info('Migration Output: '.$migrate);
            $configClear = $this->runCommand('php artisan config:clear');
            Log::info('Config Clear Output: '.$configClear);
            $routeClear = $this->runCommand('php artisan route:clear');
            Log::info('Route Clear Output: '.$routeClear);

            try {
                $npmInstall = $this->runCommand('npm install');
                Log::info('NPM Install Output: '.$npmInstall);
                $npmBuild = $this->runCommand('npm run build');
                Log::info('NPM Build Output: '.$npmBuild);
            } catch (\Exception $e) {
                Log::error('NPM Build failed: '.$e->getMessage());

                return redirect()->route('admin.update.index')->with('warning', 'System aktualisiert, aber Frontend-Build (npm) schlug fehl: '.$e->getMessage());
            }

            // Ping Master after successful update
            \App\Jobs\SendTelemetryJob::dispatch();

            return redirect()->route('admin.update.index')->with('success', 'System erfolgreich aktualisiert. Lokale Anpassungen wurden beibehalten.');
        } catch (\Exception $e) {
            Log::error('Update failed: '.$e->getMessage());

            return back()->with('error', 'Update fehlgeschlagen: '.$e->getMessage());
        }
    }

    public function saveSettings(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'ignored_update_files' => 'nullable|string',
        ]);

        \App\Models\Setting::set('ignored_update_files', (string) ($validated['ignored_update_files'] ?? ''), 'general');

        return redirect()->route('admin.update.index')->with('success', 'Update-Einstellungen wurden gespeichert.');
    }

    private function runCommand($cmd)
    {
        $gitBinary = config('app.git_binary', 'git');

        // On Linux, if it's a Windows path, it won't exist. Fallback to 'git'.
        if ($gitBinary !== 'git' && ! file_exists($gitBinary)) {
            Log::debug("Configured git binary not found: $gitBinary. Falling back to 'git'.");
            $gitBinary = 'git';
        }

        // Replace 'git ' prefix with the actual binary path
        if (str_starts_with($cmd, 'git ')) {
            $executable = PHP_OS_FAMILY === 'Windows' ? '"'.$gitBinary.'"' : $gitBinary;
            $cmd = $executable.substr($cmd, 3);
        }

        Log::debug("Executing command: $cmd in ".base_path());
        $result = Process::path(base_path())->run($cmd);

        if (! $result->successful()) {
            $error = $result->errorOutput() ?: $result->output();
            $exitCode = $result->exitCode();

            // Auto-fix for "dubious ownership"
            if ($exitCode === 128 && str_contains($error, 'safe.directory')) {
                Log::info('Detected dubious ownership. Attempting auto-fix...');
                $fixCmd = $executable.' config --global --add safe.directory '.str_replace('\\', '/', base_path());
                Process::path(base_path())->run($fixCmd);

                // Try original command again
                $result = Process::path(base_path())->run($cmd);
                if ($result->successful()) {
                    return trim($result->output());
                }
            }

            // Auto-fix for "could not write index" (Self-Healing)
            if (str_contains($error, 'could not write index')) {
                Log::warning('Git index error detected. Attempting self-healing...');
                
                @unlink(base_path('.git/index.lock'));
                @unlink(base_path('.git/index.old'));
                
                // If the error persists, try clearing the primary index as a last resort
                // but ONLY if we haven't already tried to fix this specific command
                if (! str_contains($cmd, 'reset')) {
                    Log::info('Removing corrupted index and resetting...');
                    @unlink(base_path('.git/index'));
                    Process::path(base_path())->run($executable.' reset --mixed');
                    
                    // Try original command again
                    Log::info('Retrying original command after index reset: '.$cmd);
                    $result = Process::path(base_path())->run($cmd);
                    if ($result->successful()) {
                        Log::info('Self-healing successful!');
                        return trim($result->output());
                    }
                }
            }

            // Extra Diagnostics for "could not write index"
            if (str_contains($error, 'could not write index')) {
                $df = shell_exec('df -h .');
                $indexPath = base_path().'/.git/index';
                $oldIndexPath = base_path().'/.git/index.old';
                $dirPath = base_path().'/.git';
                
                $dirPerms = substr(sprintf('%o', fileperms($dirPath)), -4);
                $indexPerms = file_exists($indexPath) ? substr(sprintf('%o', fileperms($indexPath)), -4) : 'FEHLT';
                $oldIndexPerms = file_exists($oldIndexPath) ? substr(sprintf('%o', fileperms($oldIndexPath)), -4) : 'KEINE';
                
                $dirOwner = posix_getpwuid(fileowner($dirPath))['name'] ?? 'unknown';
                $indexOwner = file_exists($indexPath) ? (posix_getpwuid(fileowner($indexPath))['name'] ?? 'unknown') : 'N/A';
                
                $currentUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
                
                $error .= "\n[DIAGNOSTIK] Speicher:\n$df";
                $error .= "\n[DIAGNOSTIK] .git Ordner: $dirPerms ($dirOwner)";
                $error .= "\n[DIAGNOSTIK] .git/index Datei: $indexPerms ($indexOwner)";
                $error .= "\n[DIAGNOSTIK] .git/index.old: $oldIndexPerms";
                $error .= "\n[DIAGNOSTIK] Aktueller PHP-User: $currentUser";
            }

            Log::warning("Command failed (Exit $exitCode): $cmd. Error: ".$error);
            throw new \App\Exceptions\SystemUpdateException("Systemfehler ($exitCode): ".$error);
        }

        return trim($result->output());
    }
}

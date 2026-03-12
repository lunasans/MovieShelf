<?php
require __DIR__.'/vendor/autoload.php';
use Illuminate\Support\Facades\Process;

function runCommand($cmd) {
    $gitPath = '"C:\Program Files\Git\cmd\git.exe"';
    if (str_starts_with($cmd, 'git ')) {
        $cmd = $gitPath . substr($cmd, 3);
    }
    echo "Running: $cmd\n";
    $result = shell_exec($cmd . ' 2>&1');
    echo "Output: $result\n";
}

runCommand('git rev-parse --short HEAD');
runCommand('git status');

<?php
$dbPath = __DIR__ . '/database/database.sqlite';
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Deleting domains...\n";
    $pdo->exec("DELETE FROM domains");
    
    echo "Deleting tenants...\n";
    $pdo->exec("DELETE FROM tenants");

    echo "Cleanup complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

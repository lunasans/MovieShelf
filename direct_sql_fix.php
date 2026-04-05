<?php

$dbPath = __DIR__ . '/database/database.sqlite';
if (!file_exists($dbPath)) {
    die("Database not found at $dbPath\n");
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to central database.\n";

    // 1. Create missing tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, key TEXT UNIQUE, value TEXT, created_at DATETIME, updated_at DATETIME)");
    echo "Table 'settings' ready.\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS counter (id INTEGER PRIMARY KEY, page TEXT UNIQUE, visits INTEGER DEFAULT 0, created_at DATETIME, updated_at DATETIME)");
    echo "Table 'counter' ready.\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (id INTEGER PRIMARY KEY, title TEXT, is_deleted INTEGER DEFAULT 0, created_at DATETIME, updated_at DATETIME)");
    echo "Table 'movies' ready.\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS actors (id INTEGER PRIMARY KEY, name TEXT, created_at DATETIME, updated_at DATETIME)");
    echo "Table 'actors' ready.\n";

    // 2. Insert domain data for hallo1
    $tenantId = 'hallo1';
    $domains = [$tenantId, $tenantId . '.localhost'];

    foreach ($domains as $domain) {
        $stmt = $pdo->prepare("SELECT id FROM domains WHERE domain = ?");
        $stmt->execute([$domain]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO domains (domain, tenant_id, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))");
            $stmt->execute([$domain, $tenantId]);
            echo "Inserted domain '$domain' for tenant '$tenantId'.\n";
        } else {
            echo "Domain '$domain' already exists.\n";
        }
    }

    echo "SQL Fix finished successfully.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

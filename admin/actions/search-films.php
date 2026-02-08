<?php
/**
 * Film-Such-API für Autocomplete
 * Sucht Filme in der Datenbank
 * 
 * Speicherort: /admin/actions/search-films.php
 */

session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';

// Admin-Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Keine Berechtigung']);
    exit;
}

$query = trim($_GET['q'] ?? '');

if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Suche nach Titel
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            year,
            cover_path
        FROM dvds
        WHERE title LIKE :query
        ORDER BY 
            CASE 
                WHEN title LIKE :exact THEN 1
                WHEN title LIKE :start THEN 2
                ELSE 3
            END,
            title ASC
        LIMIT 20
    ");
    
    $stmt->execute([
        ':query' => '%' . $query . '%',
        ':exact' => $query,
        ':start' => $query . '%'
    ]);
    
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($films);
    
} catch (PDOException $e) {
    error_log("Film Search API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler']);
}
?>
<?php
/**
 * Actor-Film-Management API
 * Fügt Filme zur Filmographie hinzu/entfernt sie/bearbeitet Rollen
 * 
 * Speicherort: /admin/actions/actor-films.php
 * 
 * Actions:
 * - add: Film zur Filmographie hinzufügen
 * - remove: Film aus Filmographie entfernen
 * - update_role: Rolle bearbeiten
 */

session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';

// Admin-Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Keine Berechtigung']);
    exit;
}

// JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$action = $input['action'];
$actorId = (int)($input['actor_id'] ?? 0);
$filmId = (int)($input['film_id'] ?? 0);

if ($actorId === 0 || $filmId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Actor-ID und Film-ID erforderlich']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            // Film zur Filmographie hinzufügen
            $role = trim($input['role'] ?? '');
            
            // Prüfe ob bereits vorhanden
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM film_actor 
                WHERE actor_id = :actor_id AND film_id = :film_id
            ");
            $stmt->execute([
                ':actor_id' => $actorId,
                ':film_id' => $filmId
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Film ist bereits in der Filmographie'
                ]);
                exit;
            }
            
            // Höchste sort_order ermitteln
            $stmt = $pdo->prepare("
                SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order
                FROM film_actor
                WHERE film_id = :film_id
            ");
            $stmt->execute([':film_id' => $filmId]);
            $castOrder = $stmt->fetchColumn();
            
            // Eintrag erstellen
            $stmt = $pdo->prepare("
                INSERT INTO film_actor (film_id, actor_id, role, sort_order)
                VALUES (:film_id, :actor_id, :role, :sort_order)
            ");
            $stmt->execute([
                ':film_id' => $filmId,
                ':actor_id' => $actorId,
                ':role' => $role,
                ':sort_order' => $castOrder
            ]);
            
            // Filmtitel für Response laden
            $stmt = $pdo->prepare("SELECT title, year FROM dvds WHERE id = :id");
            $stmt->execute([':id' => $filmId]);
            $film = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Film hinzugefügt',
                'film' => $film
            ]);
            break;
            
        case 'remove':
            // Film aus Filmographie entfernen
            $stmt = $pdo->prepare("
                DELETE FROM film_actor 
                WHERE actor_id = :actor_id AND film_id = :film_id
            ");
            $stmt->execute([
                ':actor_id' => $actorId,
                ':film_id' => $filmId
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Film entfernt'
            ]);
            break;
            
        case 'update_role':
            // Rolle aktualisieren
            $role = trim($input['role'] ?? '');
            
            $stmt = $pdo->prepare("
                UPDATE film_actor 
                SET role = :role
                WHERE actor_id = :actor_id AND film_id = :film_id
            ");
            $stmt->execute([
                ':role' => $role,
                ':actor_id' => $actorId,
                ':film_id' => $filmId
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rolle aktualisiert'
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Unbekannte Aktion: ' . $action
            ]);
    }
    
} catch (PDOException $e) {
    error_log("Actor-Films API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler'
    ]);
}
?>
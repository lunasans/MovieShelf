<?php
/**
 * Enrich Actor Profile from TMDb
 * Returns actor data from TMDb for auto-filling the edit form
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/tmdb-helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
}

try {
    $tmdbId = (int)($_POST['tmdb_id'] ?? 0);
    
    if ($tmdbId <= 0) {
        throw new Exception('Ungültige TMDb ID');
    }
    
    // API Key Check
    $apiKey = getSetting('tmdb_api_key', '');
    if (empty($apiKey)) {
        throw new Exception('Kein TMDb API Key gesetzt');
    }
    
    // TMDb Helper
    $tmdb = new TMDbHelper($apiKey);
    
    // Actor-Details laden
    $actorData = $tmdb->getActorDetails($tmdbId);
    
    if (!$actorData) {
        throw new Exception('Schauspieler nicht auf TMDb gefunden');
    }
    
    // Profilbild-URL generieren
    $profileImageUrl = null;
    if (!empty($actorData['profile_path'])) {
        $profileImageUrl = 'https://image.tmdb.org/t/p/w500' . $actorData['profile_path'];
    }
    
    // Response mit allen Daten
    echo json_encode([
        'success' => true,
        'data' => [
            'name' => $actorData['name'] ?? '',
            'biography' => $actorData['biography'] ?? '',
            'birth_date' => $actorData['birthday'] ?? '',
            'birth_place' => $actorData['place_of_birth'] ?? '',
            'death_date' => $actorData['deathday'] ?? '',
            'profile_image_url' => $profileImageUrl,
            'tmdb_id' => $tmdbId,
            'known_for_department' => $actorData['known_for_department'] ?? '',
            'popularity' => $actorData['popularity'] ?? 0
        ]
    ]);
    
} catch (Exception $e) {
    error_log('TMDb Actor Enrichment Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

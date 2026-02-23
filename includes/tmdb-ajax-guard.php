<?php
/**
 * includes/tmdb-ajax-guard.php
 *
 * Zentrale Sicherheitsprüfungen für alle TMDb-AJAX-Endpunkte.
 * Prüft Session, CSRF-Token und TMDb API-Key.
 * Gibt bei Erfolg $apiKey zurück (als Variable im aufrufenden Scope).
 *
 * Muss NACH session_start() und require bootstrap.php eingebunden werden.
 */

// Nur für eingeloggte User
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// CSRF Token prüfen
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'CSRF validation failed']));
}

// API Key prüfen
$apiKey = getSetting('tmdb_api_key', '');
if (empty($apiKey)) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Kein TMDb API Key gesetzt']));
}

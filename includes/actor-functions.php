<?php
/**
 * Actor-Fragment - lädt Actor-Profile via AJAX
 * Analog zu film-fragment.php für konsistentes UX
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 * @author     René Neuhaus
 */

// Sicherheitsheader setzen
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

try {
    // Slug-Validierung am Anfang
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
    $actorId = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : 0;
    
    if (empty($slug) && !$actorId) {
        http_response_code(400);
        throw new InvalidArgumentException('Ungültige Anfrage: Weder Slug noch ID angegeben');
    }
    
    // Slug-Validierung (nur erlaubte Zeichen)
    if (!empty($slug) && !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
        http_response_code(400);
        throw new InvalidArgumentException('Ungültiger Slug: ' . htmlspecialchars($slug));
    }

    // Memory-optimiertes Output Buffering
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    // Database connection
    try {
        require_once __DIR__ . '/includes/bootstrap.php';
        
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Datenbankverbindung nicht verfügbar');
        }
        
        $pdo->query('SELECT 1');
        
    } catch (PDOException $e) {
        throw new Exception('Datenbankfehler: ' . $e->getMessage());
    }

    // Functions.php laden (für findCoverImage etc.)
    $functionsPath = __DIR__ . '/includes/functions.php';
    if (file_exists($functionsPath)) {
        require_once $functionsPath;
    }

    // Actor-Functions laden
    $actorFunctionsPath = __DIR__ . '/includes/actor-functions.php';
    if (!file_exists($actorFunctionsPath)) {
        throw new Exception('actor-functions.php nicht gefunden');
    }
    require_once $actorFunctionsPath;

    // Actor-Daten laden
    try {
        if (!empty($slug)) {
            $actor = getActorBySlug($pdo, $slug);
            
            if (!$actor) {
                http_response_code(404);
                throw new Exception("Schauspieler mit Slug '$slug' nicht gefunden");
            }
        } else {
            $actor = getActorById($pdo, $actorId);
            
            if (!$actor) {
                http_response_code(404);
                throw new Exception("Schauspieler mit ID $actorId nicht gefunden");
            }
        }
        
        $actorName = trim($actor['first_name'] . ' ' . $actor['last_name']);
        error_log("Actor-Fragment: Actor geladen - ID: {$actor['id']}, Name: $actorName");
        
    } catch (PDOException $e) {
        throw new Exception('Fehler beim Laden der Actor-Daten: ' . $e->getMessage());
    }

    // actor-profile.php laden
    $actorProfilePath = __DIR__ . '/partials/actor-profile.php';
    
    if (!file_exists($actorProfilePath)) {
        throw new Exception('partials/actor-profile.php nicht gefunden');
    }
    
    if (!is_readable($actorProfilePath)) {
        throw new Exception('actor-profile.php nicht lesbar');
    }

    // Output Buffer für actor-profile.php
    ob_start();
    
    try {
        include $actorProfilePath;
        $actorProfileOutput = ob_get_clean();
        
        if (empty($actorProfileOutput)) {
            throw new Exception('actor-profile.php hat keinen Output produziert');
        }
        
        // XSS-Schutz
        $safeActorId = htmlspecialchars($actor['id'], ENT_QUOTES, 'UTF-8');
        $safeActorSlug = htmlspecialchars($actor['slug'], ENT_QUOTES, 'UTF-8');
        
        // Wrapper für AJAX-Content (analog zu film-detail-content)
        echo '<div class="actor-detail-content fade-in" data-actor-id="' . $safeActorId . '" data-actor-slug="' . $safeActorSlug . '" data-loaded="' . time() . '">';
        echo $actorProfileOutput;
        echo '</div>';
        
    } catch (Throwable $e) {
        ob_end_clean();
        throw new Exception('Fehler beim Laden von actor-profile.php: ' . $e->getMessage());
    }

    error_log("Actor-Fragment: Erfolgreich geladen für Actor-ID: {$actor['id']}");

} catch (Throwable $e) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("Actor-Fragment FATAL ERROR: " . $e->getMessage());
    error_log("Actor-Fragment Error Type: " . get_class($e));
    
    if ($e instanceof InvalidArgumentException) {
        http_response_code(400);
    } elseif (strpos($e->getMessage(), 'nicht gefunden') !== false) {
        http_response_code(404);
    } else {
        http_response_code(500);
    }
    
    $errorClass = $e instanceof InvalidArgumentException ? 'client-error' : 'server-error';
    $errorIcon = $e instanceof InvalidArgumentException ? 'bi-exclamation-circle' : 'bi-exclamation-triangle';
    $safeErrorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    
    echo '<div class="error-message ' . $errorClass . '" style="padding: 40px; text-align: center;">
            <div class="error-icon" style="font-size: 4rem; margin-bottom: 20px; color: #f48771;">
                <i class="' . $errorIcon . '"></i>
            </div>
            <div class="error-content">
                <h3 style="color: #f48771; margin-bottom: 15px;">Schauspieler nicht gefunden</h3>
                <p style="margin-bottom: 25px;">Das Schauspieler-Profil konnte nicht geladen werden.</p>
                <div class="error-actions">
                    <button onclick="window.location.reload()" class="btn btn-sm btn-primary" style="margin-right: 10px;">
                        <i class="bi bi-arrow-clockwise"></i> Erneut versuchen
                    </button>
                    <a href="/" class="btn btn-sm btn-secondary">
                        <i class="bi bi-house"></i> Zur Startseite
                    </a>
                </div>
            </div>
          </div>';
}
?>
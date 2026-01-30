<?php
/**
 * DVD Profiler Liste - Actor Profile Fragment
 * 
 * Lädt und zeigt Schauspieler-Profile an
 * 
 * Kann verwendet werden als:
 * 1. Include von index.php: ?page=actor
 * 2. Direkter AJAX-Aufruf: /partials/actor.php?slug=...
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 * @author     René Neuhaus
 */

// Prüfe ob von index.php eingebunden oder direkt aufgerufen
$isDirectCall = !isset($pdo);

// Falls direkt aufgerufen: Bootstrap laden
if ($isDirectCall) {
    require_once __DIR__ . '/../includes/bootstrap.php';
}

// Actor Functions laden (falls noch nicht geladen)
if (!function_exists('getActorBySlug')) {
    $actorFunctionsPath = __DIR__ . '/../includes/actor-functions.php';
    if (file_exists($actorFunctionsPath)) {
        require_once $actorFunctionsPath;
    } elseif (file_exists(__DIR__ . '/includes/actor-functions.php')) {
        // Alternative: Falls actor.php im Root liegt
        require_once __DIR__ . '/includes/actor-functions.php';
    }
}

// Actor-Parameter aus URL
$actorSlug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$actorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Actor laden (entweder via Slug oder ID)
$actor = null;

if (!empty($actorSlug)) {
    $actor = getActorBySlug($pdo, $actorSlug);
} elseif ($actorId > 0) {
    $actor = getActorById($pdo, $actorId);
}

// Falls Actor nicht gefunden
if (!$actor) {
    http_response_code(404);
    ?>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-person-x"></i>
        </div>
        <h1>Schauspieler nicht gefunden</h1>
        <p>Der angeforderte Schauspieler existiert nicht oder wurde entfernt.</p>
        <a href="/" class="btn btn-primary">
            <i class="bi bi-house"></i> Zurück zur Startseite
        </a>
    </div>
    
    <style>
    .error-container {
        max-width: 600px;
        margin: 5rem auto;
        padding: 3rem;
        text-align: center;
        background: var(--glass-bg, rgba(255, 255, 255, 0.05));
        border-radius: var(--radius-lg, 12px);
        border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
    }
    
    .error-icon {
        font-size: 5rem;
        color: var(--text-muted, rgba(255, 255, 255, 0.3));
        margin-bottom: 1.5rem;
    }
    
    .error-container h1 {
        color: var(--text-white, #ffffff);
        margin-bottom: 1rem;
    }
    
    .error-container p {
        color: var(--text-glass, rgba(255, 255, 255, 0.7));
        margin-bottom: 2rem;
    }
    
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--accent-primary, #667eea);
        color: white;
        text-decoration: none;
        border-radius: var(--radius-md, 8px);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: var(--accent-hover, #764ba2);
        transform: translateY(-2px);
    }
    </style>
    <?php
    return;
}

// SEO Meta-Daten updaten
$fullName = trim($actor['first_name'] . ' ' . $actor['last_name']);
$pageTitle = $fullName . ' - Schauspieler-Profil | ' . ($siteTitle ?? 'DVD Profiler Liste');
$metaDescription = 'Profil von ' . $fullName;

if (!empty($actor['bio'])) {
    $metaDescription .= ' - ' . mb_substr(strip_tags($actor['bio']), 0, 150);
}

// Actor-Profil Template laden
// Versuche verschiedene Pfade
if (file_exists(__DIR__ . '/actor-profile.php')) {
    // Falls in /partials/ → actor-profile.php ist im gleichen Verzeichnis
    require __DIR__ . '/actor-profile.php';
} elseif (file_exists(__DIR__ . '/../actor-profile.php')) {
    // Fallback: Root-Level
    require __DIR__ . '/../actor-profile.php';
} else {
    echo "<div class='error-container'>";
    echo "<h1>Template-Fehler</h1>";
    echo "<p>actor-profile.php nicht gefunden</p>";
    echo "<p>Gesucht in:</p>";
    echo "<ul style='text-align:left;'>";
    echo "<li>" . __DIR__ . "/actor-profile.php</li>";
    echo "<li>" . __DIR__ . "/../actor-profile.php</li>";
    echo "</ul>";
    echo "</div>";
}
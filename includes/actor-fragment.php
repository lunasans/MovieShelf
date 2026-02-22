<?php
/**
 * DVD Profiler Liste - Actor Profile Fragment
 * 
 * Lädt und zeigt Schauspieler-Profile an
 * Wird von index.php eingebunden wenn ?page=actor
 * 
 * @package    dvdprofiler.liste
 * @version    1.5.0
 * @author     René Neuhaus
 */

// Sicherheitscheck
if (!defined('DVDPROFILER_VERSION')) {
    die('Direct access not permitted');
}

// Actor-Daten sind bereits von index.php geladen worden
// (siehe index.php - Actor-Profil Meta-Daten Sektion)
// $actor Variable ist bereits verfügbar

// Falls Actor nicht gefunden (sollte nicht vorkommen, da index.php bereits prüft)
if (!isset($actor) || !$actor) {
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

// Meta-Daten werden jetzt in index.php gesetzt (vor HTML-Head)
// $actor ist bereits von index.php verfügbar

// Actor-Profil Template laden
require __DIR__ . '/../partials/actor-profile.php';
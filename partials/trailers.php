<?php
/**
 * trailers.php - Neueste Trailer Seite (Partial)
 * Zeigt die neuesten hinzugefügten Trailer
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 */

// ============================================================================
// BOOTSTRAP & DATENBANKVERBINDUNG
// ============================================================================

global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Fallback: Bootstrap laden falls nicht vorhanden
    if (file_exists(__DIR__ . '/../includes/bootstrap.php')) {
        require_once __DIR__ . '/../includes/bootstrap.php';
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * YouTube URL zu Embed URL konvertieren
 * @param string $url YouTube URL
 * @return string Embed URL oder leer
 */
function getYouTubeEmbedUrl($url) {
    if (empty($url)) return '';
    
    // Extract Video ID
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $matches);
    $videoId = $matches[1] ?? '';
    
    if (!$videoId) return '';
    
    return "https://www.youtube.com/embed/{$videoId}";
}

// ============================================================================
// PAGINATION & DATEN LADEN
// ============================================================================

// Anzahl Trailer pro Seite
$trailersPerPage = 12;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $trailersPerPage;

// Debug Output
error_log('Trailers Page - $_GET: ' . print_r($_GET, true));
error_log("Trailers Page - Current page: $page, Offset: $offset");

try {
    // Gesamtanzahl Filme mit Trailer
    $countStmt = $pdo->query("SELECT COUNT(*) FROM dvds WHERE trailer_url IS NOT NULL AND trailer_url != ''");
    $totalTrailers = (int)$countStmt->fetchColumn();
    $totalPages = (int)ceil($totalTrailers / $trailersPerPage);
    
    // Neueste Filme mit Trailer laden (sortiert nach ID DESC = neueste zuerst)
    $stmt = $pdo->prepare("
        SELECT id, title, year, genre, cover_id, trailer_url, created_at
        FROM dvds 
        WHERE trailer_url IS NOT NULL AND trailer_url != ''
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $trailersPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $trailers = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Trailer page error: ' . $e->getMessage());
    $trailers = [];
    $totalTrailers = 0;
    $totalPages = 0;
}

// ============================================================================
// AJAX-Request? Nur JSON zurückgeben (für Infinite Scroll)
// ============================================================================
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    try {
        header('Content-Type: application/json');
        
        $htmlCards = '';
        foreach ($trailers as $trailer) {
            // Cover-Bild finden
            $coverUrl = findCoverImage($trailer['cover_id'] ?? '', 'f');
            
            $embedUrl = getYouTubeEmbedUrl($trailer['trailer_url']);
            
            $htmlCards .= '<div class="trailer-card" data-trailer-id="' . $trailer['id'] . '">';
            $htmlCards .= '<div class="trailer-thumbnail" data-action="play-trailer" data-embed-url="' . htmlspecialchars($embedUrl) . '">';
            $htmlCards .= '<img src="' . htmlspecialchars($coverUrl) . '" alt="' . htmlspecialchars($trailer['title']) . ' Cover" loading="lazy" onerror="this.src=\'' . COVER_IMG_PATH . '/placeholder.png\'">';
            $htmlCards .= '<div class="play-overlay"><i class="bi bi-play-circle-fill"></i></div>';
            $htmlCards .= '<div class="trailer-duration">Trailer</div>';
            $htmlCards .= '</div>';
            $htmlCards .= '<div class="trailer-info">';
            $htmlCards .= '<h3 class="trailer-title"><a href="?page=film&id=' . $trailer['id'] . '">' . htmlspecialchars($trailer['title']) . '</a></h3>';
            $htmlCards .= '<div class="trailer-meta">';
            $htmlCards .= '<span class="trailer-year"><i class="bi bi-calendar3"></i> ' . $trailer['year'] . '</span>';
            $htmlCards .= '<span class="trailer-genre"><i class="bi bi-tag"></i> ' . htmlspecialchars($trailer['genre']) . '</span>';
            $htmlCards .= '</div>';
            $htmlCards .= '</div>';
            $htmlCards .= '</div>';
        }
        
        echo json_encode([
            'success' => true,
            'html' => $htmlCards,
            'page' => $page,
            'hasMore' => ($offset + $trailersPerPage) < $totalTrailers,
            'total' => $totalTrailers,
            'loaded' => count($trailers)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    exit;
}

$displayedTrailers = count($trailers);
?>

<!-- ============================================================================
     PAGE HEADER
     ============================================================================ -->
<main class="trailers-page" data-total="<?= $totalTrailers ?>" data-per-page="<?= $trailersPerPage ?>" data-current-page="<?= $page ?>">
    <div class="page-header">
        <div class="page-header-content">
            <h1>
                <i class="bi bi-play-circle"></i>
                Neueste Trailer
            </h1>
            <p class="page-subtitle">
                <span id="displayedCount"><?= $displayedTrailers ?></span> / <?= $totalTrailers ?> Trailer in der Sammlung
                <?php /* Debug Info - kann entfernt werden wenn alles funktioniert */ ?>
                <!--
                <small style="opacity: 0.6; margin-left: 1rem;">
                    (Seite: <?= $page ?>, Offset: <?= $offset ?>, Total Pages: <?= $totalPages ?>)
                </small>
                -->
            </p>
        </div>
    </div>
    
    <!-- ========================================================================
         EMPTY STATE oder TRAILER GRID
         ======================================================================== -->
    <?php if (empty($trailers)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="bi bi-play-circle"></i>
            <h2>Keine Trailer verfügbar</h2>
            <p>Es wurden noch keine Trailer hinzugefügt.</p>
        </div>
        
    <?php else: ?>
        <!-- Trailers Grid -->
        <div class="trailers-grid">
            <?php foreach ($trailers as $trailer): 
                // Cover-Bild finden mit zentraler Funktion
                $coverUrl = findCoverImage($trailer['cover_id'] ?? '', 'f');
                
                $embedUrl = getYouTubeEmbedUrl($trailer['trailer_url']);
            ?>
                <div class="trailer-card" data-trailer-id="<?= $trailer['id'] ?>">
                    <!-- Trailer Thumbnail mit Play Button -->
                    <div class="trailer-thumbnail" 
                         data-action="play-trailer" 
                         data-embed-url="<?= htmlspecialchars($embedUrl) ?>">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($trailer['title']) ?> Cover"
                             loading="lazy"
                             onerror="this.src='<?= COVER_IMG_PATH ?>/placeholder.png'">
                        
                        <!-- Play Overlay -->
                        <div class="play-overlay">
                            <i class="bi bi-play-circle-fill"></i>
                        </div>
                        
                        <!-- Trailer Badge -->
                        <div class="trailer-duration">Trailer</div>
                    </div>
                    
                    <!-- Film Info -->
                    <div class="trailer-info">
                        <h3 class="trailer-title">
                            <a href="?page=film&id=<?= $trailer['id'] ?>">
                                <?= htmlspecialchars($trailer['title']) ?>
                            </a>
                        </h3>
                        <div class="trailer-meta">
                            <span class="trailer-year">
                                <i class="bi bi-calendar3"></i>
                                <?= $trailer['year'] ?>
                            </span>
                            <span class="trailer-genre">
                                <i class="bi bi-tag"></i>
                                <?= htmlspecialchars($trailer['genre']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Infinite Scroll Loading Indicator -->
        <div id="infiniteScrollLoader" style="display: none; text-align: center; padding: 40px;">
            <div style="display: inline-block;">
                <div style="width: 50px; height: 50px; border: 4px solid rgba(78, 201, 176, 0.2); border-top-color: #4EC9B0; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 15px; color: var(--text-muted, #999);">Lade weitere Trailer...</p>
            </div>
        </div>
        
        <!-- Infinite Scroll Trigger (unsichtbar) -->
        <div id="infiniteScrollTrigger" style="height: 1px;"></div>
    <?php endif; ?>
</main>


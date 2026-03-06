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
    header('Content-Type: application/json');
    
    $htmlCards = '';
    foreach ($trailers as $trailer) {
        // Cover-Bild finden
        $coverUrl = 'cover/placeholder.png';
        if (isset($trailer['cover_id']) && $trailer['cover_id']) {
            if (function_exists('findCoverImage')) {
                $coverUrl = findCoverImage($trailer['cover_id'], 'f');
            } else {
                $coverUrl = "cover/{$trailer['cover_id']}f.jpg";
            }
        }
        
        $embedUrl = getYouTubeEmbedUrl($trailer['trailer_url']);
        
        $htmlCards .= '<div class="trailer-card" data-trailer-id="' . $trailer['id'] . '">';
        $htmlCards .= '<div class="trailer-thumbnail" onclick="playTrailer(this)" data-embed-url="' . htmlspecialchars($embedUrl) . '">';
        $htmlCards .= '<img src="' . htmlspecialchars($coverUrl) . '" alt="' . htmlspecialchars($trailer['title']) . ' Cover" loading="lazy" onerror="this.src=\'/../cover/placeholder.png\'">';
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
                // Cover-Bild finden mit Fallback
                $coverUrl = 'cover/placeholder.png'; // Default
                if (isset($trailer['cover_id']) && $trailer['cover_id']) {
                    if (function_exists('findCoverImage')) {
                        $coverUrl = findCoverImage($trailer['cover_id'], 'f');
                    } else {
                        // Manueller Fallback falls Funktion fehlt
                        $coverUrl = "cover/{$trailer['cover_id']}f.jpg";
                    }
                }
                
                $embedUrl = getYouTubeEmbedUrl($trailer['trailer_url']);
            ?>
                <div class="trailer-card" data-trailer-id="<?= $trailer['id'] ?>">
                    <!-- Trailer Thumbnail mit Play Button -->
                    <div class="trailer-thumbnail" 
                         onclick="playTrailer(this)" 
                         data-embed-url="<?= htmlspecialchars($embedUrl) ?>">
                        <img src="<?= htmlspecialchars($coverUrl) ?>" 
                             alt="<?= htmlspecialchars($trailer['title']) ?> Cover"
                             loading="lazy"
                             onerror="this.src='/../cover/placeholder.png'">
                        
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

<!-- ============================================================================
     TRAILER MODAL
     ============================================================================ -->
<div id="trailerModal" class="trailer-modal">
    <div class="trailer-modal-backdrop" onclick="closeTrailerModal()"></div>
    <div class="trailer-modal-content">
        <button class="trailer-modal-close" onclick="closeTrailerModal()" aria-label="Schließen">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="trailer-video-wrapper" id="trailerVideoWrapper">
            <!-- Cover-Placeholder (wird initial angezeigt) -->
            <div class="trailer-video-placeholder" id="trailerPlaceholder">
                <img id="trailerCoverImage" src="" alt="Film Cover">
                <div class="modal-play-overlay">
                    <div class="modal-play-button" onclick="playVideoInModal()">
                        <i class="bi bi-play-fill"></i>
                    </div>
                </div>
            </div>
            <!-- Video Container (wird nach Play-Click angezeigt) -->
            <div class="trailer-video-container" id="trailerVideoContainer" style="display: none;">
                <!-- YouTube iframe wird hier eingefügt -->
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     STYLES
     ============================================================================ -->
<style>
/* ============================================================================
   MAIN LAYOUT
   ============================================================================ */
.trailers-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--space-xl, 2rem) var(--space-lg, 1.5rem);
}

/* ============================================================================
   PAGE HEADER
   ============================================================================ */
.page-header {
    text-align: center;
    margin-bottom: var(--space-2xl, 3rem);
}

.page-header-content h1 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-md, 1rem);
    font-size: 2.5rem;
    margin-bottom: var(--space-sm, 0.5rem);
    color: var(--text-primary, #e4e4e7);
}

.page-header-content h1 i {
    color: var(--accent-primary, #667eea);
    font-size: 2.8rem;
}

.page-subtitle {
    font-size: 1.1rem;
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
}

/* ============================================================================
   TRAILERS GRID
   ============================================================================ */
.trailers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-lg, 1.5rem);
    margin-bottom: var(--space-2xl, 3rem);
}

/* ============================================================================
   TRAILER CARD
   ============================================================================ */
.trailers-page .trailer-card {
    background: var(--bg-secondary, rgba(255, 255, 255, 0.05));
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    transition: all 0.3s ease;
}

.trailers-page .trailer-card:hover {
    border-color: var(--accent-primary, #667eea);
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    transform: translateY(-4px);
}

/* ============================================================================
   TRAILER THUMBNAIL
   ============================================================================ */
.trailers-page .trailer-thumbnail {
    position: relative;
    width: 100%;
    padding-bottom: 150%; /* DVD Cover Hochformat (2:3 Aspect Ratio) */
    overflow: hidden;
    cursor: pointer;
    background: var(--bg-tertiary, #16213e);
}

.trailers-page .trailer-thumbnail img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.trailers-page .trailer-card:hover .trailer-thumbnail img {
    transform: scale(1.05);
}

/* ============================================================================
   PLAY OVERLAY - Immer sichtbar mit Hover-Effekt
   ============================================================================ */
.trailers-page .play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.3);
    opacity: 1;
    transition: all 0.3s ease;
    pointer-events: none; /* Clicks gehen durch zum parent */
}

.trailers-page .trailer-card:hover .play-overlay {
    background: rgba(0, 0, 0, 0.5);
}

.trailers-page .play-overlay i {
    font-size: 4rem;
    color: white;
    opacity: 0.9;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
    transition: all 0.3s ease;
}

.trailers-page .trailer-card:hover .play-overlay i {
    transform: scale(1.2);
    opacity: 1;
}

.trailers-page .trailer-thumbnail:active .play-overlay {
    background: rgba(0, 0, 0, 0.6);
}

.trailers-page .trailer-thumbnail:active .play-overlay i {
    transform: scale(0.9);
    transition: transform 0.1s ease;
}

/* ============================================================================
   TRAILER BADGE
   ============================================================================ */
.trailers-page .trailer-duration {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* ============================================================================
   TRAILER INFO
   ============================================================================ */
.trailers-page .trailer-info {
    padding: var(--space-md, 1rem);
}

.trailers-page .trailer-title {
    margin: 0 0 var(--space-sm, 0.5rem) 0;
    font-size: 1.1rem;
}

.trailers-page .trailer-title a {
    color: var(--text-primary, #e4e4e7);
    text-decoration: none;
    transition: color 0.3s ease;
}

.trailers-page .trailer-title a:hover {
    color: var(--accent-primary, #667eea);
}

.trailers-page .trailer-meta {
    display: flex;
    gap: var(--space-md, 1rem);
    font-size: 0.9rem;
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
}

.trailers-page .trailer-meta span {
    display: flex;
    align-items: center;
    gap: var(--space-xs, 0.35rem);
}

/* ============================================================================
   TRAILER MODAL
   ============================================================================ */
.trailer-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto; /* Falls Modal größer als Viewport */
}

.trailer-modal.show {
    display: flex;
}

.trailer-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    animation: fadeIn 0.3s ease;
}

.trailer-modal-content {
    position: relative;
    width: 100%;
    max-width: 1200px;
    z-index: 2;
    animation: zoomIn 0.3s ease;
}

.trailer-modal-close {
    position: absolute;
    top: -50px;
    right: 0;
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.trailer-modal-close:hover {
    background: rgba(255, 71, 87, 0.9);
    transform: rotate(90deg);
}

.trailer-video-wrapper {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

.trailer-video-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.trailer-video-placeholder img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.modal-play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.4);
    transition: background 0.3s ease;
}

.modal-play-overlay:hover {
    background: rgba(0, 0, 0, 0.6);
}

.modal-play-button {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 3px solid rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-play-button:hover {
    transform: scale(1.1);
    background: rgba(255, 255, 255, 0.3);
}

.modal-play-button i {
    font-size: 3rem;
    color: white;
    margin-left: 5px; /* Play-Icon optisch zentrieren */
}

.trailer-video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

.trailer-video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

/* ============================================================================
   ANIMATIONS
   ============================================================================ */

/* ============================================================================
   EMPTY STATE
   ============================================================================ */
.empty-state {
    text-align: center;
    padding: var(--space-3xl, 4rem) var(--space-lg, 1.5rem);
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
    margin-bottom: var(--space-md, 1rem);
}

.empty-state h2 {
    color: var(--text-primary, #e4e4e7);
    margin-bottom: var(--space-sm, 0.5rem);
}

.empty-state p {
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
}

/* ============================================================================
   PAGINATION
   ============================================================================ */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 2rem 0 1rem 0;
    flex-wrap: wrap;
}

.pagination-link,
.pagination-current {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
}

.pagination-link {
    background: var(--bg-secondary, rgba(255, 255, 255, 0.05));
    color: var(--text-primary, #e4e4e7);
}

.pagination-link:hover {
    background: var(--accent-primary, #667eea);
    border-color: var(--accent-primary, #667eea);
    color: white;
    transform: translateY(-2px);
}

.pagination-current {
    background: var(--accent-primary, #667eea);
    color: white;
    border-color: var(--accent-primary, #667eea);
    font-weight: 600;
    cursor: default;
}

.pagination-info {
    text-align: center;
    color: var(--text-muted, rgba(228, 228, 231, 0.6));
    font-size: 0.9rem;
    margin-bottom: var(--space-xl, 2rem);
}

/* ============================================================================
   RESPONSIVE DESIGN - TABLET
   ============================================================================ */
@media (max-width: 768px) {
    .trailers-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: var(--space-md, 1rem);
    }
    
    .page-header-content h1 {
        font-size: 2rem;
    }
    
    .trailer-modal-close {
        top: 10px;
        right: 10px;
    }
}

/* ============================================================================
   RESPONSIVE DESIGN - MOBILE
   ============================================================================ */
@media (max-width: 480px) {
    .trailers-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header-content h1 {
        font-size: 1.5rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-header-content h1 i {
        font-size: 2rem;
    }
}
</style>

<!-- Infinite Scroll Animation -->
<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- ============================================================================
     JAVASCRIPT
     ============================================================================ -->
<script>
// ============================================================================
// INFINITE SCROLL für Trailer
// ============================================================================
(function() {
    console.log('🔄 Infinite Scroll für Trailer initialisiert');
    
    // WICHTIG: Modal an das Ende des Body verschieben
    // Dies verhindert, dass parent-Container mit transform/filter position:fixed brechen
    const modal = document.getElementById('trailerModal');
    if (modal && modal.parentElement !== document.body) {
        console.log('📦 Verschiebe Modal an das Ende des Body');
        document.body.appendChild(modal);
    }
    
    const container = document.querySelector('.trailers-page');
    const trigger = document.getElementById('infiniteScrollTrigger');
    const loader = document.getElementById('infiniteScrollLoader');
    const displayedCount = document.getElementById('displayedCount');
    
    if (!container || !trigger) {
        console.error('❌ Container oder Trigger nicht gefunden');
        return;
    }
    
    let currentPage = parseInt(container.dataset.currentPage) || 1;
    const perPage = parseInt(container.dataset.perPage) || 12;
    const total = parseInt(container.dataset.total) || 0;
    
    let isLoading = false;
    let hasMore = (currentPage * perPage) < total;
    
    console.log(`📊 Initial: Page ${currentPage}, PerPage ${perPage}, Total ${total}, HasMore ${hasMore}`);
    
    // IntersectionObserver für Infinite Scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore && !isLoading) {
                console.log('👀 Trigger sichtbar - lade mehr Trailer...');
                loadMore();
            }
        });
    }, {
        rootMargin: '300px'  // Lade 300px bevor Trigger erreicht wird
    });
    
    observer.observe(trigger);
    
    async function loadMore() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        currentPage++;
        
        console.log(`⏳ Lade Trailer-Seite ${currentPage}...`);
        
        // Zeige Loading
        loader.style.display = 'block';
        
        try {
            // Baue URL - DIREKT zu partials/trailers.php!
            let url = `partials/trailers.php?ajax=1&p=${currentPage}`;
            
            console.log('📡 Fetching:', url);
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                console.log(`✅ Geladen: ${data.loaded} Trailer, HasMore: ${data.hasMore}`);
                
                // Finde die trailers-grid
                const trailersGrid = document.querySelector('.trailers-grid');
                
                if (trailersGrid && data.html) {
                    // Füge neue Trailer-Cards hinzu
                    trailersGrid.insertAdjacentHTML('beforeend', data.html);
                    
                    // Update Counter
                    const currentDisplayed = parseInt(displayedCount.textContent) || 0;
                    displayedCount.textContent = currentDisplayed + data.loaded;
                    
                    hasMore = data.hasMore;
                    
                    if (!hasMore) {
                        console.log('✋ Alle Trailer geladen');
                        trigger.style.display = 'none';
                    }
                } else {
                    console.error('❌ trailers-grid nicht gefunden oder kein HTML');
                    hasMore = false;
                }
            } else {
                console.error('❌ Server-Fehler:', data);
                hasMore = false;
            }
        } catch (error) {
            console.error('❌ Fetch-Fehler:', error);
            hasMore = false;
        } finally {
            loader.style.display = 'none';
            isLoading = false;
        }
    }
    
    console.log('✅ Infinite Scroll für Trailer bereit');
})();

// ============================================================================
// TRAILER MODAL & PLAYBACK
// ============================================================================

// Globale Variable für Embed-URL
let currentEmbedUrl = '';
let currentCoverUrl = '';

// ============================================================================
// TRAILER MODAL ÖFFNEN (zeigt Cover)
// ============================================================================
function playTrailer(element) {
    const embedUrl = element.dataset.embedUrl;
    const coverImg = element.querySelector('img');
    const coverUrl = coverImg ? coverImg.src : '';
    
    if (!embedUrl) {
        console.error('Keine Embed-URL gefunden');
        return;
    }
    
    // URLs speichern
    currentEmbedUrl = embedUrl;
    currentCoverUrl = coverUrl;
    
    const modal = document.getElementById('trailerModal');
    const placeholder = document.getElementById('trailerPlaceholder');
    const videoContainer = document.getElementById('trailerVideoContainer');
    const coverImage = document.getElementById('trailerCoverImage');
    
    // Cover anzeigen
    if (coverUrl) {
        coverImage.src = coverUrl;
    }
    
    // Sicherstellen dass Placeholder sichtbar und Video versteckt ist
    placeholder.style.display = 'flex';
    videoContainer.style.display = 'none';
    videoContainer.innerHTML = ''; // Video zurücksetzen
    
    // Scroll-Position speichern und Body fixieren (verhindert Scroll-Jump)
    const scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
    
    // Modal anzeigen
    modal.classList.add('show');
}

// ============================================================================
// VIDEO IM MODAL ABSPIELEN (nach Play-Button Click)
// ============================================================================
function playVideoInModal() {
    if (!currentEmbedUrl) return;
    
    const placeholder = document.getElementById('trailerPlaceholder');
    const videoContainer = document.getElementById('trailerVideoContainer');
    
    // YouTube iframe einfügen mit Autoplay
    videoContainer.innerHTML = `
        <iframe 
            src="${currentEmbedUrl}?autoplay=1&rel=0&modestbranding=1" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    `;
    
    // Cover verstecken, Video anzeigen
    placeholder.style.display = 'none';
    videoContainer.style.display = 'block';
}

// ============================================================================
// TRAILER MODAL SCHLIEẞEN
// ============================================================================
function closeTrailerModal() {
    const modal = document.getElementById('trailerModal');
    const placeholder = document.getElementById('trailerPlaceholder');
    const videoContainer = document.getElementById('trailerVideoContainer');
    
    // Video stoppen (iframe entfernen)
    videoContainer.innerHTML = '';
    
    // Zurück zum Cover-State
    placeholder.style.display = 'flex';
    videoContainer.style.display = 'none';
    
    // Modal verstecken
    modal.classList.remove('show');
    
    // Scroll-Position wiederherstellen
    const scrollY = document.body.style.top;
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    window.scrollTo(0, parseInt(scrollY || '0') * -1);
    
    // URLs zurücksetzen
    currentEmbedUrl = '';
    currentCoverUrl = '';
}

// ============================================================================
// ESC-KEY HANDLER
// ============================================================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('trailerModal');
        if (modal.classList.contains('show')) {
            closeTrailerModal();
        }
    }
});
</script>
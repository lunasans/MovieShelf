<?php
/**
 * Actors Overview Page - Vereinfachte Version
 * Mit direktem JavaScript statt route-link
 */

// Bootstrap & PDO laden
global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (file_exists(__DIR__ . '/../includes/bootstrap.php')) {
        require_once __DIR__ . '/../includes/bootstrap.php';
    }
}

// Actor-Functions laden
if (!function_exists('getActorById')) {
    require_once __DIR__ . '/../includes/actor-functions.php';
}

// Filter-Parameter
$letter = isset($_GET['letter']) ? strtoupper(substr($_GET['letter'], 0, 1)) : '';

// ERST: Alle verfügbaren Buchstaben laden (für Navigation)
$availableLetters = [];
$letterStmt = $pdo->query("
    SELECT DISTINCT 
        UPPER(SUBSTRING(last_name, 1, 1)) as first_letter
    FROM actors
    WHERE last_name IS NOT NULL AND last_name != ''
");
while ($row = $letterStmt->fetch(PDO::FETCH_ASSOC)) {
    $char = $row['first_letter'];
    if (preg_match('/^[A-Z]$/', $char)) {
        $availableLetters[$char] = true;
    } else {
        $availableLetters['#'] = true;
    }
}

// DANN: Gefilterte Schauspieler laden (PAGINATION für Performance!)
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 50;  // 50 Schauspieler pro Load
$offset = ($page - 1) * $perPage;

$sql = "SELECT id, first_name, last_name, slug, birth_year, photo_path FROM actors WHERE 1=1";
$params = [];

if (!empty($letter) && preg_match('/^[A-Z]$/', $letter)) {
    $sql .= " AND last_name LIKE :letter";
    $params['letter'] = $letter . '%';
}

// Total Count für Infinite Scroll
$countSql = str_replace("SELECT id, first_name, last_name, slug, birth_year, photo_path", "SELECT COUNT(*)", $sql);
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalActors = (int)$countStmt->fetchColumn();

$sql .= " ORDER BY last_name ASC, first_name ASC LIMIT :limit OFFSET :offset";

// DEBUG
error_log("=== ACTORS PAGINATION DEBUG ===");
error_log("Letter: " . ($letter ?: 'empty'));
error_log("Page: $page, PerPage: $perPage, Offset: $offset");
error_log("Total: $totalActors");
error_log("================================");

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gruppierung (nur wenn kein Filter aktiv)
$actorsByLetter = [];

if (empty($letter)) {
    // KEINE Filter: Gruppiere nach Nachname (A-Z)
    foreach ($actors as $actor) {
        if (!empty($actor['last_name'])) {
            $firstLetter = strtoupper(mb_substr($actor['last_name'], 0, 1));
            
            if (!preg_match('/^[A-Z]$/', $firstLetter)) {
                $firstLetter = '#';
            }
        } else {
            // Kein Nachname → in "#"
            $firstLetter = '#';
        }
        
        $actorsByLetter[$firstLetter][] = [
            'name' => trim($actor['first_name'] . ' ' . $actor['last_name']),
            'slug' => $actor['slug'],
            'birth_year' => $actor['birth_year'],
            'photo_path' => $actor['photo_path']
        ];
    }
    
    ksort($actorsByLetter);
    if (isset($actorsByLetter['#'])) {
        $temp = $actorsByLetter['#'];
        unset($actorsByLetter['#']);
        $actorsByLetter['#'] = $temp;
    }
} else {
    // FILTER AKTIV: Keine Gruppierung, alle in eine "Gruppe"
    $actorsByLetter[$letter] = [];
    foreach ($actors as $actor) {
        $actorsByLetter[$letter][] = [
            'name' => trim($actor['first_name'] . ' ' . $actor['last_name']),
            'slug' => $actor['slug'],
            'birth_year' => $actor['birth_year'],
            'photo_path' => $actor['photo_path']
        ];
    }
}

// AJAX-Request? Nur HTML-Fragment zurückgeben (für Infinite Scroll)
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $htmlCards = '';
    foreach ($actorsByLetter as $currentLetter => $actorsInGroup) {
        foreach ($actorsInGroup as $actor) {
            $hasPhoto = !empty($actor['photo_path']);
            $photoPath = $hasPhoto ? 'images/actors/' . basename($actor['photo_path']) : '';
            
            $htmlCards .= '<div class="actor-card">';
            $htmlCards .= '<a href="#" class="actor-card-link actor-link" data-actor-slug="' . htmlspecialchars($actor['slug']) . '">';
            $htmlCards .= '<div class="actor-photo">';
            
            if ($hasPhoto) {
                $htmlCards .= '<img src="' . htmlspecialchars($photoPath) . '" alt="' . htmlspecialchars($actor['name']) . '" loading="lazy">';
            } else {
                $htmlCards .= '<div class="actor-photo-placeholder"><i class="bi bi-person-circle"></i></div>';
            }
            
            $htmlCards .= '</div>';
            $htmlCards .= '<div class="actor-info">';
            $htmlCards .= '<h3 class="actor-name">' . htmlspecialchars($actor['name']) . '</h3>';
            
            if (!empty($actor['birth_year'])) {
                $htmlCards .= '<p class="actor-birth-year"><i class="bi bi-calendar3"></i> ' . htmlspecialchars($actor['birth_year']) . '</p>';
            }
            
            $htmlCards .= '</div>';
            $htmlCards .= '<div class="actor-overlay"><i class="bi bi-arrow-right-circle"></i><span>Profil anzeigen</span></div>';
            $htmlCards .= '</a>';
            $htmlCards .= '</div>';
        }
    }
    
    echo json_encode([
        'success' => true,
        'html' => $htmlCards,
        'page' => $page,
        'hasMore' => ($offset + $perPage) < $totalActors,
        'total' => $totalActors,
        'loaded' => count($actors)
    ]);
    exit;
}

$displayedActors = count($actors);
?>

<div class="actors-overview-container" data-total="<?= $totalActors ?>" data-per-page="<?= $perPage ?>" data-current-page="<?= $page ?>" data-letter="<?= htmlspecialchars($letter) ?>">
    <div class="overview-header">
        <h1 class="overview-title">
            <i class="bi bi-people-fill"></i> Schauspieler
        </h1>
        <div class="overview-stats">
            <span class="stat-badge">
                <i class="bi bi-person-badge"></i> <span id="displayedCount"><?= $displayedActors ?></span> / <?= $totalActors ?> Schauspieler
            </span>
            <?php if (!empty($letter)): ?>
            <span class="stat-badge filter-active">
                <i class="bi bi-funnel-fill"></i> Buchstabe: <?= htmlspecialchars($letter) ?>
                <a href="index.php?page=actors" class="remove-filter">×</a>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="alphabet-nav">
        <a href="index.php?page=actors" class="letter-link<?= empty($letter) ? ' active' : '' ?>">Alle</a>
        <?php
        $alphabet = range('A', 'Z');
        $alphabet[] = '#';
        foreach ($alphabet as $char):
            $hasActors = isset($availableLetters[$char]);
            $isActive = ($letter === $char);
        ?>
        <a href="index.php?page=actors&letter=<?= $char ?>" 
           class="letter-link<?= $isActive ? ' active' : '' ?><?= !$hasActors ? ' disabled' : '' ?>">
            <?= $char ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($actorsByLetter)): ?>
        <div class="no-results">
            <i class="bi bi-search" style="font-size: 4rem; opacity: 0.2;"></i>
            <h3>Keine Schauspieler gefunden</h3>
            <p><?= !empty($letter) ? "Keine Schauspieler mit Buchstabe \"$letter\"" : "Keine Schauspieler in der Datenbank" ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($actorsByLetter as $currentLetter => $actorsInGroup): ?>
        <div class="letter-group">
            <h2 class="letter-heading">
                <span class="letter-badge"><?= $currentLetter ?></span>
                <span class="letter-count"><?= count($actorsInGroup) ?> Schauspieler</span>
            </h2>
            
            <div class="actors-grid">
                <?php foreach ($actorsInGroup as $actor): ?>
                <div class="actor-card">
                    <a href="#" class="actor-card-link actor-link" data-actor-slug="<?= htmlspecialchars($actor['slug']) ?>">
                        <div class="actor-photo">
                            <?php
                            if (!empty($actor['photo_path'])):
                                $photoPath = 'images/actors/' . basename($actor['photo_path']);
                            ?>
                                <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($actor['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="actor-photo-placeholder">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actor-info">
                            <h3 class="actor-name"><?= htmlspecialchars($actor['name']) ?></h3>
                            <?php if (!empty($actor['birth_year'])): ?>
                            <p class="actor-birth-year">
                                <i class="bi bi-calendar3"></i> <?= htmlspecialchars($actor['birth_year']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actor-overlay">
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>Profil anzeigen</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Infinite Scroll Loading Indicator -->
    <div id="infiniteScrollLoader" style="display: none; text-align: center; padding: 40px;">
        <div style="display: inline-block;">
            <div style="width: 50px; height: 50px; border: 4px solid rgba(78, 201, 176, 0.2); border-top-color: #4EC9B0; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 15px; color: var(--text-muted, #999);">Lade weitere Schauspieler...</p>
        </div>
    </div>
    
    <!-- Infinite Scroll Trigger (unsichtbar) -->
    <div id="infiniteScrollTrigger" style="height: 1px;"></div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
/* Alle Styles gleich wie vorher */
.actors-overview-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.overview-header {
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.overview-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-color, #e0e0e0);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.overview-title i {
    color: var(--primary-color, #4EC9B0);
}

.overview-stats {
    display: flex;
    gap: 10px;
}

.stat-badge {
    background: rgba(78, 201, 176, 0.1);
    color: var(--primary-color, #4EC9B0);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-badge.filter-active {
    background: rgba(255, 215, 0, 0.1);
    color: #ffd700;
}

.remove-filter {
    color: inherit;
    font-size: 1.2rem;
    font-weight: bold;
    margin-left: 4px;
    text-decoration: none;
    cursor: pointer;
}

.alphabet-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 30px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
}

.letter-link {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-color, #e0e0e0);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
}

.letter-link:hover:not(.disabled) {
    background: var(--primary-color, #4EC9B0);
    color: #1e1e1e;
    transform: translateY(-2px);
}

.letter-link.active {
    background: var(--primary-color, #4EC9B0);
    color: #1e1e1e;
    font-weight: 600;
}

.letter-link.disabled {
    opacity: 0.3;
    pointer-events: none;
}

.letter-group {
    margin-bottom: 50px;
}

.letter-heading {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(78, 201, 176, 0.2);
}

.letter-badge {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color, #4EC9B0), #569cd6);
    color: #1e1e1e;
    font-size: 1.5rem;
    font-weight: bold;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(78, 201, 176, 0.3);
}

.letter-count {
    color: var(--text-muted, #999);
}

.actors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}

.actor-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
    border: 1px solid rgba(255, 255, 255, 0.05);
    position: relative;
}

.actor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    border-color: var(--primary-color, #4EC9B0);
}

.actor-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.actor-photo {
    width: 100%;
    aspect-ratio: 2/3;
    background: rgba(255, 255, 255, 0.05);
    overflow: hidden;
}

.actor-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.actor-card:hover .actor-photo img {
    transform: scale(1.05);
}

.actor-photo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(78, 201, 176, 0.05), rgba(86, 156, 214, 0.05));
}

.actor-info {
    padding: 15px;
}

.actor-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 6px 0;
    color: var(--text-color, #e0e0e0);
}

.actor-birth-year {
    font-size: 0.85rem;
    color: var(--text-muted, #999);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

.actor-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(78, 201, 176, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.3s;
    color: #1e1e1e;
    font-weight: 600;
}

.actor-card:hover .actor-overlay {
    opacity: 1;
}

.actor-overlay i {
    font-size: 2rem;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
}

@media (max-width: 768px) {
    .actors-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
    .overview-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// ========================================
// INFINITE SCROLL für Actors
// ========================================
(function() {
    console.log('🔄 Infinite Scroll initialisiert');
    
    const container = document.querySelector('.actors-overview-container');
    const trigger = document.getElementById('infiniteScrollTrigger');
    const loader = document.getElementById('infiniteScrollLoader');
    const displayedCount = document.getElementById('displayedCount');
    
    if (!container || !trigger) {
        console.error('❌ Container oder Trigger nicht gefunden');
        return;
    }
    
    let currentPage = parseInt(container.dataset.currentPage) || 1;
    const perPage = parseInt(container.dataset.perPage) || 50;
    const total = parseInt(container.dataset.total) || 0;
    const letter = container.dataset.letter || '';
    
    let isLoading = false;
    let hasMore = (currentPage * perPage) < total;
    
    console.log(`📊 Initial: Page ${currentPage}, PerPage ${perPage}, Total ${total}, HasMore ${hasMore}`);
    
    // IntersectionObserver für Infinite Scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore && !isLoading) {
                console.log('👀 Trigger sichtbar - lade mehr...');
                loadMore();
            }
        });
    }, {
        rootMargin: '200px'  // Lade 200px bevor Trigger erreicht wird
    });
    
    observer.observe(trigger);
    
    async function loadMore() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        currentPage++;
        
        console.log(`⏳ Lade Seite ${currentPage}...`);
        
        // Zeige Loading
        loader.style.display = 'block';
        
        try {
            // Baue URL - DIREKT zu partials/actors.php!
            let url = `partials/actors.php?ajax=1&p=${currentPage}`;
            if (letter) {
                url += `&letter=${encodeURIComponent(letter)}`;
            }
            
            console.log('📡 Fetching:', url);
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                console.log(`✅ Geladen: ${data.loaded} Schauspieler, HasMore: ${data.hasMore}`);
                
                // Finde die richtige actors-grid
                let actorsGrid;
                if (letter) {
                    // Mit Filter: Eine Gruppe
                    actorsGrid = document.querySelector('.letter-group .actors-grid');
                } else {
                    // Ohne Filter: Letzte Gruppe
                    const letterGroups = document.querySelectorAll('.letter-group');
                    if (letterGroups.length > 0) {
                        actorsGrid = letterGroups[letterGroups.length - 1].querySelector('.actors-grid');
                    }
                }
                
                if (actorsGrid && data.html) {
                    // Füge neue Cards hinzu
                    actorsGrid.insertAdjacentHTML('beforeend', data.html);
                    
                    // Rebind Actor-Links (für Split-Screen)
                    if (window.dvdApp && typeof window.dvdApp.rebindActorLinks === 'function') {
                        window.dvdApp.rebindActorLinks();
                    }
                    
                    // Update Counter
                    const currentDisplayed = parseInt(displayedCount.textContent) || 0;
                    displayedCount.textContent = currentDisplayed + data.loaded;
                    
                    hasMore = data.hasMore;
                    
                    if (!hasMore) {
                        console.log('✋ Alle Schauspieler geladen');
                        trigger.style.display = 'none';
                    }
                } else {
                    console.error('❌ actors-grid nicht gefunden oder kein HTML');
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
    
    console.log('✅ Infinite Scroll bereit');
})();
</script>
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
            $photoPath = $hasPhoto ? ACTOR_IMG_PATH . '/' . basename($actor['photo_path']) : '';
            
            $htmlCards .= '<div class="actor-card">';
            $htmlCards .= '<a href="#" class="actor-card-link actor-link" data-actor-slug="' . htmlspecialchars($actor['slug']) . '">';
            $htmlCards .= '<div class="actor-photo">';
            
            if ($hasPhoto && file_exists($photoPath)) {
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
                            if (!empty($actor['photo_path']) && file_exists('images/actors/' . basename($actor['photo_path']))):
                                $photoPath = ACTOR_IMG_PATH . '/' . basename($actor['photo_path']);
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
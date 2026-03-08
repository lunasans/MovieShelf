<?php
/**
 * DVD Profiler Liste - Core Functions
 * Version: 1.4.8 (Fixed)
 */

function findCoverImage(string $coverId, string $suffix = 'f', string $folder = COVER_IMG_PATH, string $fallback = COVER_IMG_PATH . '/placeholder.png'): string
{
    $extensions = ['.jpg', '.jpeg', '.png'];
    foreach ($extensions as $ext) {
        $filename = "{$coverId}{$suffix}{$ext}";
        $fullPath = BASE_PATH . '/' . $folder . '/' . $filename;
        if (file_exists($fullPath)) {
            return $folder . '/' . $filename;
        }
    }
    return $fallback;
}

/**
 * Lädt alle Schauspieler für einen Film
 * KORRIGIERT: Verwendet korrekte Tabellenstruktur mit film_actor
 */
function getActorsByDvdId(PDO $pdo, int $dvdId): array
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.first_name, 
                a.last_name, 
                a.slug,
                fa.role,
                fa.is_main_role,
                fa.sort_order
            FROM actors a
            INNER JOIN film_actor fa ON a.id = fa.actor_id
            WHERE fa.film_id = ?
            ORDER BY fa.sort_order ASC, a.last_name ASC, a.first_name ASC
        ");
        $stmt->execute([$dvdId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getActorsByDvdId error: " . $e->getMessage());
        // Fallback: Versuche alte Struktur (wenn noch nicht migriert)
        try {
            $stmt = $pdo->prepare("SELECT id, firstname as first_name, lastname as last_name, role FROM actors WHERE dvd_id = ?");
            $stmt->execute([$dvdId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            error_log("getActorsByDvdId fallback error: " . $e2->getMessage());
            return [];
        }
    }
}

function formatRuntime(?int $minutes): string
{
    if (!$minutes) return '';
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return $h > 0 ? "{$h}h {$m}min" : "{$m}min";
}

function getChildDvds(PDO $pdo, string $parentId): array
{
    $stmt = $pdo->prepare("SELECT * FROM dvds WHERE boxset_parent = ? ORDER BY title");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

/**
 * Helper: Film Card mit Badge (Grid) und Sternen (List)
 */
function renderFilmCard(array $dvd): string {
    $title = htmlspecialchars($dvd['title'] ?? 'Unbekannt');
    $year = (int)($dvd['year'] ?? 0);
    $genre = htmlspecialchars($dvd['genre'] ?? 'Unbekannt');
    $id = (int)($dvd['id'] ?? 0);
    $cover = COVER_IMG_PATH . '/placeholder.png';
    
    if (!empty($dvd['cover_id'])) {
        $extensions = ['.jpg', '.jpeg', '.png'];
        foreach ($extensions as $ext) {
            $file = BASE_PATH . '/' . COVER_IMG_PATH . "/{$dvd['cover_id']}f{$ext}";
            if (file_exists($file)) {
                $cover = COVER_IMG_PATH . "/{$dvd['cover_id']}f{$ext}";
                break;
            }
        }
    }
    
    $childrenCount = (int)($dvd['children_count'] ?? 0);
    $isBoxSet = $childrenCount > 0;
    
    $ratingBadge = '';
    $tmdbStarsHtml = '';
    
    if (getSetting('tmdb_show_ratings_on_cards', '1') == '1' && !empty(getSetting('tmdb_api_key', ''))) {
        $ratings = getFilmRatings($dvd['title'], $year);
        if ($ratings && isset($ratings['tmdb_rating'])) {
            $rating = $ratings['tmdb_rating'];
            $votes = $ratings['tmdb_votes'] ?? 0;
            
            if ($rating >= 8) {
                $color = '#4caf50';
            } elseif ($rating >= 6) {
                $color = '#ff9800';
            } else {
                $color = '#f44336';
            }
            
            $ratingFormatted = number_format((float)$rating, 1);
            $ratingBadge = <<<HTML
<div class="tmdb-rating-badge" style="background-color: {$color};">
    <i class="bi bi-star-fill"></i>
    <span>{$ratingFormatted}</span>
</div>
HTML;
            
            $starsRating = (float)$rating / 2;
            $fullStars = floor($starsRating);
            $hasHalfStar = ($starsRating - $fullStars) >= 0.3;
            
            $starsHtml = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $fullStars) {
                    $starsHtml .= '<i class="bi bi-star-fill" style="color: ' . $color . ';"></i>';
                } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                    $starsHtml .= '<i class="bi bi-star-half" style="color: ' . $color . ';"></i>';
                } else {
                    $starsHtml .= '<i class="bi bi-star" style="color: rgba(255,255,255,0.2);"></i>';
                }
            }
            
            $votesFormatted = number_format((float)$votes);
            $tmdbStarsHtml = <<<HTML
<div class="tmdb-rating-stars">
    <span class="tmdb-label">TMDb:</span>
    <div class="tmdb-stars">{$starsHtml}</div>
    <span class="tmdb-score" style="color: {$color};">{$ratingFormatted}</span>
    <span class="tmdb-votes">({$votesFormatted})</span>
</div>
HTML;
        }
    }
    
    $badge = '';
    if ($isBoxSet) {
        $badge = <<<HTML
<div class="boxset-badge" onclick="event.stopPropagation(); openBoxSetModal(event, {$id});">
    <i class="bi bi-collection-play"></i>
    <span>{$childrenCount}</span>
</div>
HTML;
    }
    
    $boxsetClass = $isBoxSet ? ' has-boxset' : '';
    $coverEscaped = htmlspecialchars($cover);
    
    return <<<HTML
<div class="dvd{$boxsetClass}" data-dvd-id="{$id}" data-children-count="{$childrenCount}">
  <div class="cover-area">
    <img src="{$coverEscaped}" alt="Cover">
    {$ratingBadge}
    {$badge}
  </div>
  <div class="dvd-details">
    <div class="film-info">
      <h2><a href="#" class="toggle-detail" data-id="{$id}">{$title} ({$year})</a></h2>
      <p class="genre-info"><strong>Genre:</strong> {$genre}</p>
    </div>
    {$tmdbStarsHtml}
  </div>
</div>
HTML;
}

/**
 * getSetting ist bereits in bootstrap.php definiert
 * Diese Funktion wird nur als Fallback verwendet falls bootstrap.php nicht geladen wurde
 */
if (!function_exists('getSetting')) {
    function getSetting(string $key, string $default = ''): string
    {
        global $pdo;

        try {
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = :key LIMIT 1");
            $stmt->execute(['key' => $key]);
            $value = $stmt->fetchColumn();

            return is_string($value) ? $value : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }
}

/**
 * Guard für Admin-AJAX-Endpunkte.
 * Prüft eingeloggten User und erzwingt POST-Methode.
 * Terminiert mit JSON-Fehler wenn nicht erfüllt.
 */
function requireAdminAjax(): void
{
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Nicht angemeldet']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt']);
        exit;
    }
}

/**
 * Guard für TMDb-AJAX-Endpunkte (setzt ob_start() voraus).
 * Prüft Session, CSRF-Token und TMDb API-Key.
 * Terminiert mit JSON-Fehler wenn nicht erfüllt.
 *
 * @return string Der TMDb API-Key
 */
function requireTmdbAjax(): string
{
    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        header('Content-Type: application/json');
        http_response_code(403);
        die(json_encode(['success' => false, 'error' => 'Unauthorized']));
    }

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        ob_clean();
        header('Content-Type: application/json');
        http_response_code(403);
        die(json_encode(['success' => false, 'error' => 'CSRF validation failed']));
    }

    $apiKey = getSetting('tmdb_api_key', '');
    if (empty($apiKey)) {
        ob_clean();
        header('Content-Type: application/json');
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Kein TMDb API Key gesetzt']));
    }

    return $apiKey;
}

/**
 * BuildQuery-Funktion für Pagination
 */
function buildQuery($params = []) {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        if ($value === '') {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    return http_build_query($currentParams);
}

<?php
/**
 * DVD Profiler Liste - Core Functions
 * Version: 1.4.8 (Fixed)
 */

function findCoverImage(string $coverId, string $suffix = 'f', string $folder = 'cover', string $fallback = 'cover/placeholder.png'): string
{
    $extensions = ['.jpg', '.jpeg', '.png'];
    foreach ($extensions as $ext) {
        $file = "{$folder}/{$coverId}{$suffix}{$ext}";
        if (file_exists($file)) {
            return $file;
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

function renderFilmCard(array $dvd, bool $isChild = false): string
{
    $cover = htmlspecialchars(findCoverImage($dvd['cover_id'], 'f'));
    $title = htmlspecialchars($dvd['title']);
    $year = (int)$dvd['year'];
    $genre = htmlspecialchars($dvd['genre'] ?? '');
    $id = (int)$dvd['id'];

    $hasChildren = !$isChild && !empty(getChildDvds($GLOBALS['pdo'], $id));

    return '
    <div class="dvd' . ($isChild ? ' child-dvd' : '') . '" data-dvd-id="' . $id . '">
      <div class="cover-area">
        <img src="' . $cover . '" alt="Cover">
      </div>
      <div class="dvd-details">
        <h2><a href="#" class="toggle-detail" data-id="' . $id . '">' . $title . ' (' . $year . ')</a></h2>
        <p><strong>Genre:</strong> ' . $genre . '</p>'
        . ($hasChildren ? '<button class="boxset-toggle">► Box-Inhalte anzeigen</button>' : '') .
      '</div>
    </div>';
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
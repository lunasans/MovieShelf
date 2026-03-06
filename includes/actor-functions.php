<?php
/**
 * Actor Helper Functions
 *
 * Hilfsfunktionen für Actor-Daten
 *
 * @package    movieshelf
 * @version    1.5.0
 * @author     René Neuhaus
 */

// Sicherheitscheck
if (!defined('DVDPROFILER_VERSION')) {
    // Wenn außerhalb des Systems aufgerufen, Bootstrap laden
    require_once __DIR__ . '/bootstrap.php';
}

/**
 * Interne Hilfsfunktion: Lädt eine Actor-Zeile anhand eines Feldes.
 * Erlaubte Felder: 'id', 'slug' (Whitelist gegen SQL-Injection).
 *
 * @internal
 */
function _fetchActorRow(PDO $pdo, string $field, mixed $value): ?array
{
    static $allowed = ['id', 'slug'];
    if (!in_array($field, $allowed, true)) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, slug, birth_date, birth_place,
               death_date, nationality, bio, photo_path, imdb_id, website,
               view_count, created_at, updated_at
        FROM actors
        WHERE {$field} = ?
        LIMIT 1
    ");
    $stmt->execute([$value]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Lädt einen Schauspieler anhand seiner ID
 *
 * @param PDO $pdo Database connection
 * @param int $actorId Actor ID
 * @return array|null Actor data or null if not found
 */
function getActorById(PDO $pdo, int $actorId): ?array
{
    try {
        return _fetchActorRow($pdo, 'id', $actorId);
    } catch (PDOException $e) {
        error_log("getActorById error: " . $e->getMessage());
        return null;
    }
}

/**
 * Lädt einen Schauspieler anhand seines Slugs
 *
 * @param PDO $pdo Database connection
 * @param string $slug Actor slug
 * @return array|null Actor data or null if not found
 */
function getActorBySlug(PDO $pdo, string $slug): ?array
{
    try {
        return _fetchActorRow($pdo, 'slug', $slug);
    } catch (PDOException $e) {
        error_log("getActorBySlug error: " . $e->getMessage());
        return null;
    }
}

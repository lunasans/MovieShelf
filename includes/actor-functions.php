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
 * Lädt einen Schauspieler anhand seiner ID
 *
 * @param PDO $pdo Database connection
 * @param int $actorId Actor ID
 * @return array|null Actor data or null if not found
 */
function getActorById(PDO $pdo, int $actorId): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, slug, birth_date, birth_place,
                   death_date, nationality, bio, photo_path, imdb_id, website,
                   view_count, created_at, updated_at
            FROM actors
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$actorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
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
function getActorBySlug(PDO $pdo, string $slug): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, slug, birth_date, birth_place,
                   death_date, nationality, bio, photo_path, imdb_id, website,
                   view_count, created_at, updated_at
            FROM actors
            WHERE slug = ?
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    } catch (PDOException $e) {
        error_log("getActorBySlug error: " . $e->getMessage());
        return null;
    }
}

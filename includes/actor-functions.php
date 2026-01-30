<?php
/**
 * DVD Profiler Liste - Actor Profile Functions
 * 
 * Helper-Funktionen für Schauspieler-Profile
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 * @author     René Neuhaus
 */

/**
 * Lädt einen Schauspieler anhand der ID
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $actorId Schauspieler-ID
 * @return array|null Actor-Daten oder null
 */
function getActorById(PDO $pdo, int $actorId): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM actors 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->execute([$actorId]);
        $actor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $actor ?: null;
    } catch (PDOException $e) {
        error_log("getActorById error: " . $e->getMessage());
        return null;
    }
}

/**
 * Lädt einen Schauspieler anhand des Slugs
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param string $slug URL-freundlicher Identifier
 * @return array|null Actor-Daten oder null
 */
function getActorBySlug(PDO $pdo, string $slug): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM actors 
            WHERE slug = ? 
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $actor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $actor ?: null;
    } catch (PDOException $e) {
        error_log("getActorBySlug error: " . $e->getMessage());
        return null;
    }
}

/**
 * Generiert einen eindeutigen Slug für einen Schauspieler
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param string $firstName Vorname
 * @param string $lastName Nachname
 * @param int|null $actorId Aktuelle Actor-ID (zum Ausschließen bei Updates)
 * @return string Generierter Slug
 */
function generateActorSlug(PDO $pdo, string $firstName, string $lastName, ?int $actorId = null): string {
    // Basis-Slug erstellen
    $baseSlug = createSlugFromName($firstName, $lastName);
    $slug = $baseSlug;
    $counter = 1;
    
    // Prüfen ob Slug bereits existiert
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM actors WHERE slug = ? AND id != ? LIMIT 1");
        $stmt->execute([$slug, $actorId ?? 0]);
        
        if ($stmt->rowCount() === 0) {
            break; // Slug ist verfügbar
        }
        
        // Slug existiert bereits, füge Counter hinzu
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Erstellt einen URL-freundlichen Slug aus einem Namen
 * 
 * @param string $firstName Vorname
 * @param string $lastName Nachname
 * @return string Slug
 */
function createSlugFromName(string $firstName, string $lastName): string {
    $fullName = $firstName . ' ' . $lastName;
    
    // Umlaute und Sonderzeichen ersetzen
    $replacements = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
        'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u',
        'ñ' => 'n', 'ç' => 'c'
    ];
    
    $slug = str_replace(array_keys($replacements), array_values($replacements), $fullName);
    
    // Nur alphanumerische Zeichen und Bindestriche
    $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $slug);
    
    // Mehrfache Leerzeichen zu einem reduzieren
    $slug = preg_replace('/\s+/', '-', $slug);
    
    // Mehrfache Bindestriche zu einem reduzieren
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Bindestriche am Anfang/Ende entfernen
    $slug = trim($slug, '-');
    
    // Kleinschreibung
    $slug = strtolower($slug);
    
    return $slug;
}

/**
 * Lädt alle Schauspieler (für Admin-Liste)
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $limit Anzahl der Ergebnisse
 * @param int $offset Offset für Pagination
 * @param string $search Suchbegriff (optional)
 * @return array Liste der Schauspieler
 */
function getAllActors(PDO $pdo, int $limit = 50, int $offset = 0, string $search = ''): array {
    try {
        $sql = "SELECT 
                    a.*, 
                    COUNT(DISTINCT fa.film_id) as film_count
                FROM actors a
                LEFT JOIN film_actor fa ON a.id = fa.actor_id";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE CONCAT(a.first_name, ' ', a.last_name) LIKE ?";
            $params[] = "%{$search}%";
        }
        
        $sql .= " GROUP BY a.id
                  ORDER BY a.last_name ASC, a.first_name ASC
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getAllActors error: " . $e->getMessage());
        return [];
    }
}

/**
 * Zählt die Gesamtanzahl der Schauspieler
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param string $search Suchbegriff (optional)
 * @return int Anzahl
 */
function countActors(PDO $pdo, string $search = ''): int {
    try {
        $sql = "SELECT COUNT(*) FROM actors";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
            $params[] = "%{$search}%";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("countActors error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Speichert oder aktualisiert einen Schauspieler
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param array $data Actor-Daten
 * @param int|null $actorId ID für Update, null für Insert
 * @return int|false Actor-ID oder false bei Fehler
 */
function saveActor(PDO $pdo, array $data, ?int $actorId = null) {
    try {
        // Slug generieren falls nicht vorhanden
        if (empty($data['slug'])) {
            $data['slug'] = generateActorSlug(
                $pdo, 
                $data['first_name'], 
                $data['last_name'], 
                $actorId
            );
        }
        
        if ($actorId) {
            // Update
            $sql = "UPDATE actors SET 
                    first_name = ?,
                    last_name = ?,
                    slug = ?,
                    birth_date = ?,
                    birth_place = ?,
                    death_date = ?,
                    nationality = ?,
                    bio = ?,
                    photo_path = ?,
                    website = ?,
                    imdb_id = ?,
                    tmdb_id = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['slug'],
                $data['birth_date'] ?? null,
                $data['birth_place'] ?? null,
                $data['death_date'] ?? null,
                $data['nationality'] ?? null,
                $data['bio'] ?? null,
                $data['photo_path'] ?? null,
                $data['website'] ?? null,
                $data['imdb_id'] ?? null,
                $data['tmdb_id'] ?? null,
                $actorId
            ];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $actorId;
        } else {
            // Insert
            $sql = "INSERT INTO actors (
                    first_name, last_name, slug, birth_date, birth_place, 
                    death_date, nationality, bio, photo_path, website, 
                    imdb_id, tmdb_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['slug'],
                $data['birth_date'] ?? null,
                $data['birth_place'] ?? null,
                $data['death_date'] ?? null,
                $data['nationality'] ?? null,
                $data['bio'] ?? null,
                $data['photo_path'] ?? null,
                $data['website'] ?? null,
                $data['imdb_id'] ?? null,
                $data['tmdb_id'] ?? null
            ];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return (int)$pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        error_log("saveActor error: " . $e->getMessage());
        return false;
    }
}

/**
 * Löscht einen Schauspieler
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $actorId Schauspieler-ID
 * @return bool Erfolg
 */
function deleteActor(PDO $pdo, int $actorId): bool {
    try {
        $pdo->beginTransaction();
        
        // Erst Film-Verknüpfungen löschen
        $stmt = $pdo->prepare("DELETE FROM film_actor WHERE actor_id = ?");
        $stmt->execute([$actorId]);
        
        // Dann den Schauspieler
        $stmt = $pdo->prepare("DELETE FROM actors WHERE id = ?");
        $stmt->execute([$actorId]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("deleteActor error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validiert Actor-Daten
 * 
 * @param array $data Zu validierende Daten
 * @return array Fehlermeldungen (leer wenn valide)
 */
function validateActorData(array $data): array {
    $errors = [];
    
    if (empty($data['first_name'])) {
        $errors[] = 'Vorname ist erforderlich';
    }
    
    if (empty($data['last_name'])) {
        $errors[] = 'Nachname ist erforderlich';
    }
    
    if (!empty($data['birth_date']) && !validateDate($data['birth_date'])) {
        $errors[] = 'Ungültiges Geburtsdatum';
    }
    
    if (!empty($data['death_date']) && !validateDate($data['death_date'])) {
        $errors[] = 'Ungültiges Todesdatum';
    }
    
    if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Ungültige Website-URL';
    }
    
    if (!empty($data['imdb_id']) && !preg_match('/^nm\d+$/', $data['imdb_id'])) {
        $errors[] = 'Ungültige IMDb-ID (Format: nm1234567)';
    }
    
    return $errors;
}

/**
 * Validiert ein Datum
 * 
 * @param string $date Datum im Format YYYY-MM-DD
 * @return bool Gültig
 */
function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Lädt alle Filme eines Schauspielers
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $actorId Schauspieler-ID
 * @return array Liste der Filme
 */
function getActorFilms(PDO $pdo, int $actorId): array {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                d.id,
                d.title,
                d.year,
                d.genre,
                d.cover_id,
                d.rating_age,
                fa.role,
                fa.is_main_role,
                fa.sort_order
            FROM dvds d
            INNER JOIN film_actor fa ON d.id = fa.film_id
            WHERE fa.actor_id = ?
            AND d.deleted = 0
            ORDER BY d.year DESC, d.title ASC
        ");
        $stmt->execute([$actorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getActorFilms error: " . $e->getMessage());
        return [];
    }
}
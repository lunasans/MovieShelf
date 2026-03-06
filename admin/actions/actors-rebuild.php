<?php
/**
 * admin/actions/actors-rebuild.php
 * Leert die Actor-Tabelle und baut sie neu auf von TMDb
 * Eliminiert dabei alle Duplikate und lädt vollständige Schauspieler-Details
 * (Bio, Geburtsdatum, Geburtsort, Profilfoto, IMDb ID, etc.)
 */

// Output buffern
ob_start();

// Als AJAX markieren
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

// Error Handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "PHP Error: $errstr"
    ]);
    exit;
});

// Bootstrap laden
session_start();
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/tmdb-helper.php';

// Nur für eingeloggte User
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// CSRF Token prüfen
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'CSRF validation failed']));
}

// API Key Check
$apiKey = getSetting('tmdb_api_key', '');
if (empty($apiKey)) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(400);
    die(json_encode(['error' => 'Kein TMDb API Key gesetzt']));
}

// Batch-Verarbeitung Parameter
$offset = (int)($_POST['offset'] ?? 0);
$limit = 3; // Pro Request 3 Filme (wegen vielen API Calls: Film + 10x Actor Details)
$clearTables = isset($_POST['clear_tables']) && $_POST['clear_tables'] === '1';

try {
    // SCHRITT 1: Tabellen leeren (nur beim ersten Request)
    if ($clearTables && $offset === 0) {
        // Film-Actor Verknüpfungen löschen
        $pdo->exec("DELETE FROM film_actor");
        
        // Actors löschen
        $pdo->exec("DELETE FROM actors");
        
        // Auto-Increment zurücksetzen
        $pdo->exec("ALTER TABLE actors AUTO_INCREMENT = 1");
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'tables_cleared' => true,
            'message' => 'Tabellen geleert, starte Import...'
        ]);
        exit;
    }
    
    // SCHRITT 2: Filme holen
    $stmt = $pdo->prepare("
        SELECT id, title, year 
        FROM dvds 
        WHERE deleted = 0
        ORDER BY id 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gesamtzahl für Fortschritt
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM dvds WHERE deleted = 0");
    $total = (int)$totalStmt->fetchColumn();
    
    // Keine Filme mehr?
    if (empty($films)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'completed' => true,
            'processed' => $offset,
            'total' => $total,
            'message' => 'Alle Schauspieler neu importiert!'
        ]);
        exit;
    }
    
    // TMDb Helper
    $tmdb = new TMDbHelper($apiKey);
    
    // SCHRITT 3: Für jeden Film Schauspieler von TMDb holen
    $imported = 0;
    $errors = 0;
    $skipped = 0;
    
    foreach ($films as $film) {
        $filmId = $film['id'];
        $title = $film['title'];
        $year = $film['year'];
        
        try {
            // Film auf TMDb suchen
            $searchResults = $tmdb->searchMovies($title, $year);
            
            if (empty($searchResults)) {
                $errors++;
                error_log("Actors Rebuild - Film nicht gefunden: {$title} ({$year})");
                continue;
            }
            
            // Ersten Treffer nehmen
            $tmdbId = $searchResults[0]['tmdb_id'];
            
            // Film-Details mit Cast holen
            $movieData = $tmdb->getMovieDetails($tmdbId);
            
            if (empty($movieData['credits']['cast'])) {
                $skipped++;
                continue;
            }
            
            // Schauspieler importieren (Top 10)
            $cast = array_slice($movieData['credits']['cast'], 0, 10);
            $actors = [];
            
            foreach ($cast as $index => $person) {
                $actors[] = [
                    'name' => $person['name'] ?? '',
                    'character' => $person['character'] ?? '',
                    'order' => $index,
                    'tmdb_id' => $person['id'] ?? null,  // TMDb Actor ID
                    'profile_path' => $person['profile_path'] ?? null
                ];
            }
            
            // Schauspieler in DB einfügen (mit vollständigen Details)
            importActorsWithDetails($pdo, $tmdb, $filmId, $actors);
            $imported++;
            
        } catch (Exception $e) {
            $errors++;
            error_log("Actors Rebuild Error for film {$filmId}: " . $e->getMessage());
        }
        
        // Pause zwischen Requests (API Rate Limit + mehr Calls pro Film)
        usleep(500000); // 0.5 Sekunden (mehr Zeit wegen Actor-Details-Calls)
    }
    
    // Response
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'completed' => false,
        'processed' => $offset + count($films),
        'total' => $total,
        'imported' => $imported,
        'errors' => $errors,
        'skipped' => $skipped,
        'next_offset' => $offset + $limit,
        'progress' => round((($offset + count($films)) / $total) * 100, 1)
    ]);
    exit;
    
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    error_log('Actors Rebuild Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Rebuild: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Importiert Schauspieler für einen Film MIT vollständigen Details von TMDb
 */
function importActorsWithDetails($pdo, $tmdb, $filmId, $actors) {
    if (empty($actors)) {
        return;
    }
    
    foreach ($actors as $actorData) {
        $fullName = trim($actorData['name'] ?? '');
        $character = trim($actorData['character'] ?? '');
        $order = (int)($actorData['order'] ?? 0);
        $tmdbActorId = (int)($actorData['tmdb_id'] ?? 0);
        $profilePath = $actorData['profile_path'] ?? null;
        
        if (empty($fullName)) {
            continue;
        }
        
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Slug generieren
        $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
        
        try {
            // WICHTIG: Zuerst nach TMDb ID suchen (verhindert Duplikate)
            $actorId = null;
            
            if ($tmdbActorId > 0) {
                $stmt = $pdo->prepare("
                    SELECT id FROM actors
                    WHERE tmdb_id = :tmdb_id
                    LIMIT 1
                ");
                $stmt->execute(['tmdb_id' => $tmdbActorId]);
                $existingActor = $stmt->fetch();
                
                if ($existingActor) {
                    $actorId = $existingActor['id'];
                }
            }
            
            // Falls nicht gefunden, nach Slug suchen
            if (!$actorId) {
                $stmt = $pdo->prepare("
                    SELECT id FROM actors
                    WHERE slug = :slug
                    LIMIT 1
                ");
                $stmt->execute(['slug' => $baseSlug]);
                $existingActor = $stmt->fetch();
                
                if ($existingActor) {
                    $actorId = $existingActor['id'];
                }
            }
            
            // Falls nicht gefunden, nach Namen suchen
            if (!$actorId) {
                $stmt = $pdo->prepare("
                    SELECT id FROM actors
                    WHERE first_name = :first_name
                    AND last_name = :last_name
                    LIMIT 1
                ");
                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);
                $existingActor = $stmt->fetch();
                
                if ($existingActor) {
                    $actorId = $existingActor['id'];
                }
            }
            
            // Vollständige Details von TMDb holen (nur wenn TMDb ID vorhanden)
            $actorDetails = null;
            if ($tmdbActorId > 0) {
                $actorDetails = $tmdb->getActorDetails($tmdbActorId);
                
                // Kleine Pause zwischen API Calls
                usleep(100000); // 0.1 Sekunden
            }
            
            // Profilfoto herunterladen (falls vorhanden)
            $localPhotoPath = null;
            if ($profilePath) {
                $localPhotoPath = downloadActorPhoto($profilePath, $baseSlug);
            }
            
            if ($actorId) {
                // Actor existiert - UPDATE mit TMDb Details
                if ($actorDetails) {
                    $stmt = $pdo->prepare("
                        UPDATE actors
                        SET first_name = :first_name,
                            last_name = :last_name,
                            slug = :slug,
                            tmdb_id = :tmdb_id,
                            bio = :bio,
                            birth_date = :birth_date,
                            birth_place = :birth_place,
                            death_date = :death_date,
                            photo_path = :photo_path,
                            imdb_id = :imdb_id,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    
                    $stmt->execute([
                        'id' => $actorId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'slug' => $baseSlug,
                        'tmdb_id' => $tmdbActorId,
                        'bio' => $actorDetails['biography'] ?? null,
                        'birth_date' => $actorDetails['birthday'] ?? null,
                        'birth_place' => $actorDetails['place_of_birth'] ?? null,
                        'death_date' => $actorDetails['deathday'] ?? null,
                        'photo_path' => $localPhotoPath,
                        'imdb_id' => $actorDetails['external_ids']['imdb_id'] ?? null
                    ]);
                }
            } else {
                // Neuen Schauspieler anlegen MIT vollständigen Details
                if ($actorDetails) {
                    $stmt = $pdo->prepare("
                        INSERT INTO actors (
                            first_name, last_name, slug, tmdb_id, bio, 
                            birth_date, birth_place, death_date, photo_path, imdb_id,
                            created_at, updated_at
                        ) VALUES (
                            :first_name, :last_name, :slug, :tmdb_id, :bio,
                            :birth_date, :birth_place, :death_date, :photo_path, :imdb_id,
                            NOW(), NOW()
                        )
                    ");
                    
                    $stmt->execute([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'slug' => $baseSlug,
                        'tmdb_id' => $tmdbActorId,
                        'bio' => $actorDetails['biography'] ?? null,
                        'birth_date' => $actorDetails['birthday'] ?? null,
                        'birth_place' => $actorDetails['place_of_birth'] ?? null,
                        'death_date' => $actorDetails['deathday'] ?? null,
                        'photo_path' => $localPhotoPath,
                        'imdb_id' => $actorDetails['external_ids']['imdb_id'] ?? null
                    ]);
                } else {
                    // Fallback: Nur Basis-Daten ohne TMDb Details
                    $stmt = $pdo->prepare("
                        INSERT INTO actors (first_name, last_name, slug, tmdb_id, photo_path, created_at, updated_at)
                        VALUES (:first_name, :last_name, :slug, :tmdb_id, :photo_path, NOW(), NOW())
                    ");
                    
                    $stmt->execute([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'slug' => $baseSlug,
                        'tmdb_id' => $tmdbActorId > 0 ? $tmdbActorId : null,
                        'photo_path' => $localPhotoPath
                    ]);
                }
                
                $actorId = $pdo->lastInsertId();
            }
            
            // Verknüpfung erstellen
            $stmt = $pdo->prepare("
                INSERT INTO film_actor (film_id, actor_id, role, is_main_role, sort_order, created_at)
                VALUES (:film_id, :actor_id, :role, :is_main_role, :sort_order, NOW())
                ON DUPLICATE KEY UPDATE
                    role = VALUES(role),
                    is_main_role = VALUES(is_main_role),
                    sort_order = VALUES(sort_order)
            ");
            
            $stmt->execute([
                'film_id' => $filmId,
                'actor_id' => $actorId,
                'role' => !empty($character) ? $character : null,
                'is_main_role' => ($order < 3) ? 1 : 0,
                'sort_order' => $order
            ]);
            
        } catch (PDOException $e) {
            error_log("Actors Rebuild - Failed to import actor {$fullName}: " . $e->getMessage());
        }
    }
}

/**
 * Lädt Schauspieler-Profilfoto von TMDb herunter und speichert es lokal
 * 
 * @param string $tmdbPath TMDb Pfad (z.B. "/abc123.jpg")
 * @param string $slug Actor Slug für Dateinamen
 * @return string|null Lokaler Pfad relativ zu images/actors/ oder null bei Fehler
 */
function downloadActorPhoto($tmdbPath, $slug) {
    if (empty($tmdbPath)) {
        return null;
    }
    
    try {
        // TMDb Image URL (w185 = kleine Größe für Profilbilder)
        $imageUrl = 'https://image.tmdb.org/t/p/w185' . $tmdbPath;
        
        // Zielverzeichnis
        $actorDir = dirname(__DIR__, 2) . '/images/actors';
        if (!file_exists($actorDir)) {
            mkdir($actorDir, 0755, true);
        }
        
        // Dateiname: slug + Extension von TMDb
        $extension = pathinfo($tmdbPath, PATHINFO_EXTENSION);
        $fileName = $slug . '.' . ($extension ?: 'jpg');
        $targetFile = $actorDir . '/' . $fileName;
        
        // Bild herunterladen
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false) {
            error_log("Actor Photo Download failed: {$imageUrl}");
            return null;
        }
        
        // Speichern
        $success = file_put_contents($targetFile, $imageData);
        
        if ($success) {
            // Relativer Pfad für DB (wie in actor-save.php)
            return 'images/actors/' . $fileName;
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Actor Photo Download Error: " . $e->getMessage());
        return null;
    }
}

function importActors($pdo, $filmId, $actors) {
    if (empty($actors)) {
        return;
    }
    
    foreach ($actors as $actorData) {
        $fullName = trim($actorData['name'] ?? '');
        $character = trim($actorData['character'] ?? '');
        $order = (int)($actorData['order'] ?? 0);
        
        if (empty($fullName)) {
            continue;
        }
        
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Slug generieren
        $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
        
        try {
            // WICHTIG: Zuerst nach Slug suchen (verhindert Duplikate)
            $stmt = $pdo->prepare("
                SELECT id FROM actors
                WHERE slug = :slug
                LIMIT 1
            ");
            
            $stmt->execute(['slug' => $baseSlug]);
            $existingActor = $stmt->fetch();
            
            // Falls kein Slug-Match, nach Namen suchen
            if (!$existingActor) {
                $stmt = $pdo->prepare("
                    SELECT id, slug FROM actors
                    WHERE first_name = :first_name
                    AND last_name = :last_name
                    LIMIT 1
                ");
                
                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);
                
                $existingActor = $stmt->fetch();
                
                // Falls Actor ohne Slug existiert, Slug nachtragen
                if ($existingActor && empty($existingActor['slug'])) {
                    $stmt = $pdo->prepare("
                        UPDATE actors
                        SET slug = :slug, updated_at = NOW()
                        WHERE id = :id
                    ");
                    
                    $stmt->execute([
                        'slug' => $baseSlug,
                        'id' => $existingActor['id']
                    ]);
                }
            }
            
            if ($existingActor) {
                $actorId = $existingActor['id'];
            } else {
                // Neuen Schauspieler anlegen MIT Slug
                $stmt = $pdo->prepare("
                    INSERT INTO actors (first_name, last_name, slug, created_at, updated_at)
                    VALUES (:first_name, :last_name, :slug, NOW(), NOW())
                ");
                
                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'slug' => $baseSlug
                ]);
                
                $actorId = $pdo->lastInsertId();
            }
            
            // Verknüpfung erstellen
            $stmt = $pdo->prepare("
                INSERT INTO film_actor (film_id, actor_id, role, is_main_role, sort_order, created_at)
                VALUES (:film_id, :actor_id, :role, :is_main_role, :sort_order, NOW())
                ON DUPLICATE KEY UPDATE
                    role = VALUES(role),
                    is_main_role = VALUES(is_main_role),
                    sort_order = VALUES(sort_order)
            ");
            
            $stmt->execute([
                'film_id' => $filmId,
                'actor_id' => $actorId,
                'role' => !empty($character) ? $character : null,
                'is_main_role' => ($order < 3) ? 1 : 0,
                'sort_order' => $order
            ]);
            
        } catch (PDOException $e) {
            error_log("Actors Rebuild - Failed to import actor {$fullName}: " . $e->getMessage());
        }
    }
}

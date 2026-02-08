<?php
/**
 * API Endpoint: Actor speichern (für Wiki-Style Editing)
 * Für alle eingeloggten User
 */

// Bootstrap laden
require_once __DIR__ . '/../../includes/bootstrap.php';

// Nur für eingeloggte User
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Nicht eingeloggt']);
    exit;
}

// Nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF Token prüfen
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Actor Functions laden
if (!function_exists('saveActor')) {
    require_once __DIR__ . '/../../includes/actor-functions.php';
}

$actorId = (int)($_POST['id'] ?? 0);

try {
    // Actor-Daten sammeln
    $actorData = [
        'id' => $actorId,
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'birth_date' => $_POST['birth_date'] ?? null,
        'birth_place' => trim($_POST['birth_place'] ?? ''),
        'death_date' => $_POST['death_date'] ?? null,
        'nationality' => trim($_POST['nationality'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'website' => trim($_POST['website'] ?? ''),
        'imdb_id' => trim($_POST['imdb_id'] ?? ''),
        'tmdb_id' => trim($_POST['tmdb_id'] ?? ''),
    ];
    
    // Foto-Upload verarbeiten
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../images/actors/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $filename = uniqid('actor_') . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                // Altes Foto löschen (optional)
                if ($actorId > 0) {
                    $oldActor = getActorById($pdo, $actorId);
                    if ($oldActor && !empty($oldActor['photo_path'])) {
                        $oldPhotoPath = $uploadDir . basename($oldActor['photo_path']);
                        if (file_exists($oldPhotoPath)) {
                            @unlink($oldPhotoPath);
                        }
                    }
                }
                
                $actorData['photo_path'] = $filename;
            }
        }
    }
    
    // Slug generieren (falls leer)
    if (empty($actorData['slug'])) {
        $actorData['slug'] = generateActorSlug($actorData['first_name'], $actorData['last_name']);
    }
    
    // Actor speichern
    if ($actorId > 0) {
        // Update
        $sql = "UPDATE actors SET 
                first_name = :first_name,
                last_name = :last_name,
                birth_date = :birth_date,
                birth_place = :birth_place,
                death_date = :death_date,
                nationality = :nationality,
                bio = :bio,
                website = :website,
                imdb_id = :imdb_id,
                tmdb_id = :tmdb_id,
                updated_at = NOW()";
        
        if (!empty($actorData['photo_path'])) {
            $sql .= ", photo_path = :photo_path";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $actorId, PDO::PARAM_INT);
        $stmt->bindValue(':first_name', $actorData['first_name']);
        $stmt->bindValue(':last_name', $actorData['last_name']);
        $stmt->bindValue(':birth_date', $actorData['birth_date']);
        $stmt->bindValue(':birth_place', $actorData['birth_place']);
        $stmt->bindValue(':death_date', $actorData['death_date']);
        $stmt->bindValue(':nationality', $actorData['nationality']);
        $stmt->bindValue(':bio', $actorData['bio']);
        $stmt->bindValue(':website', $actorData['website']);
        $stmt->bindValue(':imdb_id', $actorData['imdb_id']);
        $stmt->bindValue(':tmdb_id', $actorData['tmdb_id']);
        
        if (!empty($actorData['photo_path'])) {
            $stmt->bindValue(':photo_path', $actorData['photo_path']);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Schauspieler erfolgreich aktualisiert',
            'actorId' => $actorId,
            'slug' => $actorData['slug']
        ]);
        
    } else {
        // Insert (neuer Actor)
        $sql = "INSERT INTO actors (
                    first_name, last_name, slug, birth_date, birth_place, 
                    death_date, nationality, bio, photo_path, website, 
                    imdb_id, tmdb_id, created_at
                ) VALUES (
                    :first_name, :last_name, :slug, :birth_date, :birth_place,
                    :death_date, :nationality, :bio, :photo_path, :website,
                    :imdb_id, :tmdb_id, NOW()
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':first_name', $actorData['first_name']);
        $stmt->bindValue(':last_name', $actorData['last_name']);
        $stmt->bindValue(':slug', $actorData['slug']);
        $stmt->bindValue(':birth_date', $actorData['birth_date']);
        $stmt->bindValue(':birth_place', $actorData['birth_place']);
        $stmt->bindValue(':death_date', $actorData['death_date']);
        $stmt->bindValue(':nationality', $actorData['nationality']);
        $stmt->bindValue(':bio', $actorData['bio']);
        $stmt->bindValue(':photo_path', $actorData['photo_path'] ?? null);
        $stmt->bindValue(':website', $actorData['website']);
        $stmt->bindValue(':imdb_id', $actorData['imdb_id']);
        $stmt->bindValue(':tmdb_id', $actorData['tmdb_id']);
        $stmt->execute();
        
        $newActorId = (int)$pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Schauspieler erfolgreich erstellt',
            'actorId' => $newActorId,
            'slug' => $actorData['slug']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Actor save error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Speichern: ' . $e->getMessage()
    ]);
}

/**
 * Slug aus Namen generieren
 */
function generateActorSlug($firstName, $lastName) {
    $name = trim($firstName . ' ' . $lastName);
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
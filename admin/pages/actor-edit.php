<?php
/**
 * DVD Profiler Liste - Admin Page: Schauspieler bearbeiten
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 * @author     René Neuhaus
 */

// Actor Functions laden (falls noch nicht geladen)
if (!function_exists('getAllActors')) {
    require_once __DIR__ . '/../../includes/actor-functions.php';
}

// CSRF Token (sollte bereits in Session sein)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Actor ID
$actorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $actorId > 0;

// Actor laden (bei Edit)
$actor = null;
if ($isEdit) {
    $actor = getActorById($pdo, $actorId);
    if (!$actor) {
        $_SESSION['actors_error'] = 'Schauspieler nicht gefunden';
        header('Location: ?page=actors');
        exit;
    }
}

// Initialisiere leere Werte für neuen Actor
if (!$actor) {
    $actor = [
        'id' => 0,
        'first_name' => '',
        'last_name' => '',
        'slug' => '',
        'birth_date' => '',
        'birth_place' => '',
        'death_date' => '',
        'nationality' => '',
        'bio' => '',
        'photo_path' => '',
        'website' => '',
        'imdb_id' => '',
        'tmdb_id' => '',
        'view_count' => 0,
        'created_at' => '',
        'updated_at' => ''
    ];
}

$errors = [];
$success = false;

// Form Submit Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_actor'])) {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ungültiger Sicherheitstoken';
    } else {
        // Daten sammeln
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
            'birth_place' => trim($_POST['birth_place'] ?? ''),
            'death_date' => !empty($_POST['death_date']) ? $_POST['death_date'] : null,
            'nationality' => trim($_POST['nationality'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'imdb_id' => trim($_POST['imdb_id'] ?? ''),
            'tmdb_id' => !empty($_POST['tmdb_id']) ? (int)$_POST['tmdb_id'] : null,
            'slug' => '' // Wird automatisch generiert
        ];
        
        // Foto-Upload behandeln
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../images/actors/';
            
            // Verzeichnis erstellen falls nicht vorhanden
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Dateiname: slug-based
                $tempSlug = createSlugFromName($data['first_name'], $data['last_name']);
                $fileName = $tempSlug . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                    $data['photo_path'] = 'images/actors/' . $fileName;
                } else {
                    $errors[] = 'Fehler beim Hochladen des Fotos';
                }
            } else {
                $errors[] = 'Ungültiges Dateiformat. Erlaubt: JPG, PNG, WEBP';
            }
        } elseif (!empty($_POST['tmdb_profile_image_url'])) {
            // TMDb Profilbild herunterladen
            $tmdbImageUrl = $_POST['tmdb_profile_image_url'];
            $uploadDir = __DIR__ . '/../../images/actors/';
            
            // Verzeichnis erstellen falls nicht vorhanden
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            try {
                $imageData = @file_get_contents($tmdbImageUrl);
                if ($imageData !== false) {
                    $tempSlug = createSlugFromName($data['first_name'], $data['last_name']);
                    $fileName = $tempSlug . '.jpg';
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (file_put_contents($uploadPath, $imageData)) {
                        $data['photo_path'] = 'images/actors/' . $fileName;
                        error_log("TMDb profile image downloaded: {$uploadPath}");
                    } else {
                        error_log("Failed to save TMDb profile image: {$uploadPath}");
                    }
                } else {
                    error_log("Failed to download TMDb profile image from: {$tmdbImageUrl}");
                }
            } catch (Exception $e) {
                error_log("TMDb image download error: " . $e->getMessage());
            }
        } elseif (!empty($_POST['existing_photo'])) {
            // Bestehendes Foto beibehalten
            $data['photo_path'] = $_POST['existing_photo'];
        }
        
        // Validierung
        $validationErrors = validateActorData($data);
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        }
        
        // Speichern
        if (empty($errors)) {
            $savedId = saveActor($pdo, $data, $isEdit ? $actorId : null);
            
            if ($savedId) {
                $_SESSION['actors_success'] = $isEdit ? 'Schauspieler erfolgreich aktualisiert!' : 'Schauspieler erfolgreich erstellt!';
                
                // Redirect zur Liste
                header('Location: ?page=actors');
                exit;
            } else {
                $errors[] = 'Fehler beim Speichern des Schauspielers';
            }
        }
    }
}

// Lade Film-Count für Statistik (bei Edit)
$filmCount = 0;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM film_actor WHERE actor_id = ?");
    $stmt->execute([$actorId]);
    $filmCount = $stmt->fetchColumn();
}

$pageTitle = $isEdit ? 'Schauspieler bearbeiten' : 'Neuer Schauspieler';
// Aktuelle Filmographie laden (falls Actor existiert)
$currentFilms = [];
if ($isEdit && $actorId > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            d.id,
            d.title,
            d.year,
            c.role,
            c.sort_order
        FROM dvds d
        INNER JOIN film_actor c ON d.id = c.film_id
        WHERE c.actor_id = :actor_id
        ORDER BY d.year DESC, d.title ASC
    ");
    $stmt->execute([':actor_id' => $actorId]);
    $currentFilms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
/* Actor Edit Page Styles - Konsistent mit Admin-Panel */
.edit-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.edit-form {
    background: var(--clr-card);
    border-radius: var(--radius);
    border: 1px solid var(--clr-border);
    padding: 2rem;
}

.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--clr-border);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    color: var(--clr-text);
}

.form-section h2 i {
    color: var(--clr-accent);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--clr-text);
}

.current-photo {
    margin-bottom: 1.5rem;
}

.current-photo img {
    border: 2px solid var(--clr-border);
    border-radius: var(--radius);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--clr-border);
}

.edit-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-card {
    background: var(--clr-card);
    border-radius: var(--radius);
    border: 1px solid var(--clr-border);
    padding: 1.5rem;
}

.sidebar-card h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: var(--clr-text);
}

.sidebar-card h3 i {
    color: var(--clr-accent);
}

.stat-big {
    text-align: center;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius);
    margin-bottom: 1rem;
}

.stat-big .stat-value {
    font-size: 3rem;
    font-weight: 700;
    color: var(--clr-accent);
}

.stat-big .stat-label {
    font-size: 0.9rem;
    color: var(--clr-text-muted);
    margin-top: 0.5rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--clr-border);
}

.stat-row:last-child {
    border-bottom: none;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    padding: 0.75rem 0;
    padding-left: 1.5rem;
    position: relative;
    color: var(--clr-text-muted);
    line-height: 1.6;
}

.tips-list li:before {
    content: '💡';
    position: absolute;
    left: 0;
}

@media (max-width: 992px) {
    .edit-container {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .edit-sidebar {
        order: -1;
    }
}

/* Filmographie-Management Styles */
.filmography-section {
    background: var(--clr-card);
}

.add-film-section {
    margin: 1.5rem 0;
    padding: 1rem;
    background: rgba(var(--primary-rgb), 0.05);
    border-radius: 8px;
}

.search-results {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--clr-border);
    border-radius: 6px;
    background: var(--clr-bg);
    display: none;
}

.search-results.active {
    display: block;
}

.search-result-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--clr-border);
    cursor: pointer;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: rgba(var(--primary-rgb), 0.1);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-title {
    font-weight: 600;
    color: var(--clr-text);
}

.search-result-year {
    color: var(--clr-text-muted);
    margin-left: 0.5rem;
}

.films-list {
    max-height: 400px;
    overflow-y: auto;
}

.film-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--clr-border);
    border-radius: 6px;
    transition: all 0.2s;
}

.film-item:hover {
    background: rgba(var(--primary-rgb), 0.05);
    border-color: var(--clr-primary);
}

.film-info {
    flex: 1;
}

.film-title {
    font-weight: 600;
    color: var(--clr-text);
    margin-bottom: 0.25rem;
}

.film-year {
    color: var(--clr-text-muted);
    font-weight: normal;
    font-size: 0.9em;
}

.film-role {
    font-size: 0.85em;
    color: var(--clr-text-muted);
    margin-top: 0.25rem;
}

.film-actions {
    display: flex;
    gap: 0.5rem;
}

#noFilmsMessage {
    padding: 1rem;
    text-align: center;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 6px;
}

.input-group .btn {
    white-space: nowrap;
}

#roleInputSection {
    animation: slideDown 0.3s ease;
}

</style>

<div class="container-fluid px-4">
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="?page=actors">Schauspieler</a></li>
        <li class="breadcrumb-item active"><?= $pageTitle ?></li>
    </ol>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Fehler:</strong>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-person-<?= $isEdit ? 'fill' : 'plus' ?>"></i>
                <?= $pageTitle ?>
            </h2>
            <?php if ($isEdit): ?>
            <p class="text-muted mb-0">
                ID: <?= $actor['id'] ?> | Slug: <?= htmlspecialchars($actor['slug']) ?>
            </p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="?page=actors" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Zurück zur Liste
            </a>
            <?php if ($isEdit): ?>
            <a href="../?page=actor&slug=<?= urlencode($actor['slug']) ?>" 
               class="btn btn-info" 
               target="_blank">
                <i class="bi bi-eye"></i> Profil ansehen
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="edit-container">
        <!-- Linke Spalte: Formular -->
        <div class="edit-form">
            <form method="POST" enctype="multipart/form-data" id="actorForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($actor['photo_path']) ?>">
                
                <!-- Basis-Informationen -->
                <div class="form-section">
                    <h2><i class="bi bi-person-badge"></i> Basis-Informationen</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Vorname *</label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['first_name']) ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Nachname *</label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['last_name']) ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birth_date">Geburtsdatum</label>
                            <input 
                                type="date" 
                                id="birth_date" 
                                name="birth_date" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['birth_date'] ?? '') ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="birth_place">Geburtsort</label>
                            <input 
                                type="text" 
                                id="birth_place" 
                                name="birth_place" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['birth_place']) ?>"
                                placeholder="z.B. Los Angeles, California, USA"
                            >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="death_date">Todesdatum (optional)</label>
                            <input 
                                type="date" 
                                id="death_date" 
                                name="death_date" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['death_date'] ?? '') ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="nationality">Nationalität</label>
                            <input 
                                type="text" 
                                id="nationality" 
                                name="nationality" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['nationality']) ?>"
                                placeholder="z.B. Amerikanisch"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Foto -->
                <div class="form-section">
                    <h2><i class="bi bi-image"></i> Foto</h2>
                    
                    <?php if (!empty($actor['photo_path'])): ?>
                    <div class="current-photo">
                        <img src="../<?= htmlspecialchars($actor['photo_path']) ?>" 
                             alt="Aktuelles Foto"
                             style="max-width: 200px;">
                        <p class="text-muted">Aktuelles Foto</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="photo">Neues Foto hochladen</label>
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp"
                        >
                        <small class="form-text text-muted">Erlaubt: JPG, PNG, WEBP | Max: 5MB</small>
                    </div>
                </div>
                
                <!-- Biografie -->
                <div class="form-section">
                    <h2><i class="bi bi-journal-text"></i> Biografie</h2>
                    
                    <div class="form-group">
                        <label for="bio">Biografie</label>
                        <textarea 
                            id="bio" 
                            name="bio" 
                            class="form-control"
                            rows="10"
                            placeholder="Biografische Informationen über den Schauspieler..."
                        ><?= htmlspecialchars($actor['bio']) ?></textarea>
                        <small class="form-text text-muted">
                            <span id="charCount">0</span> Zeichen
                        </small>
                    </div>
                </div>

                <!-- Externe Links -->
                <div class="form-section">
                    <h2><i class="bi bi-link-45deg"></i> Externe Links</h2>
                    
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input 
                            type="url" 
                            id="website" 
                            name="website" 
                            class="form-control"
                            value="<?= htmlspecialchars($actor['website']) ?>"
                            placeholder="https://example.com"
                        >
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="imdb_id">IMDb ID</label>
                            <input 
                                type="text" 
                                id="imdb_id" 
                                name="imdb_id" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['imdb_id']) ?>"
                                placeholder="nm0000123"
                                pattern="nm[0-9]+"
                            >
                            <small class="form-text text-muted">Format: nm1234567</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="tmdb_id">TMDb ID</label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    id="tmdb_id" 
                                    name="tmdb_id" 
                                    class="form-control"
                                    value="<?= htmlspecialchars($actor['tmdb_id'] ?? '') ?>"
                                    placeholder="123456"
                                >
                                <button type="button" class="btn btn-info" id="loadFromTMDbBtn" title="Von TMDb laden">
                                    <i class="bi bi-cloud-download"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">TMDb ID oder "Von TMDb laden" klicken</small>
                        </div>
                    </div>
                    
                    <?php if ($isEdit): ?>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Tipp:</strong> Klicken Sie auf <i class="bi bi-cloud-download"></i> um nach diesem Schauspieler auf TMDb zu suchen und das Profil automatisch auszufüllen.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-actions">
                    <button type="submit" name="save_actor" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg"></i>
                        <?= $isEdit ? 'Änderungen speichern' : 'Schauspieler erstellen' ?>
                    </button>
                    <a href="?page=actors" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-lg"></i> Abbrechen
                    </a>
                </div>
            </form>
        </div>
        
                <!-- Rechte Spalte: Filmographie & Statistiken -->
        <div class="edit-sidebar">
            <?php if ($isEdit): ?>
            
            <!-- Filmographie-Management -->
            <div class="sidebar-card filmography-section">
                <h3><i class="bi bi-film"></i> Filmographie-Verwaltung</h3>
                
                <!-- Statistik -->
                <div class="stat-big">
                    <div class="stat-value" id="filmCountDisplay"><?= count($currentFilms) ?></div>
                    <div class="stat-label">Filme in Sammlung</div>
                </div>
                
                <!-- Film hinzufügen -->
                <div class="add-film-section">
                    <label class="form-label">
                        <i class="bi bi-plus-circle"></i> Film hinzufügen
                    </label>
                    <div class="input-group mb-3">
                        <input type="text" 
                               id="filmSearchInput" 
                               class="form-control" 
                               placeholder="Film suchen..."
                               autocomplete="off">
                        <button class="btn btn-primary" id="addFilmBtn" disabled>
                            <i class="bi bi-plus-lg"></i> Hinzufügen
                        </button>
                    </div>
                    <div id="filmSearchResults" class="search-results"></div>
                    
                    <!-- Rolle eingeben -->
                    <div id="roleInputSection" style="display: none;" class="mt-2">
                        <label class="form-label">Rolle / Charakter</label>
                        <input type="text" 
                               id="roleInput" 
                               class="form-control" 
                               placeholder="z.B. James Bond">
                    </div>
                </div>
                
                <!-- Liste aktueller Filme -->
                <div class="current-films-section mt-3">
                    <h4><i class="bi bi-collection-play"></i> Aktuelle Filmographie</h4>
                    
                    <?php if (empty($currentFilms)): ?>
                        <p class="text-muted" id="noFilmsMessage">
                            <i class="bi bi-info-circle"></i> Noch keine Filme zugeordnet
                        </p>
                    <?php endif; ?>
                    
                    <div id="currentFilmsList" class="films-list">
                        <?php foreach ($currentFilms as $film): ?>
                        <div class="film-item" data-film-id="<?= $film['id'] ?>">
                            <div class="film-info">
                                <div class="film-title">
                                    <?= htmlspecialchars($film['title']) ?>
                                    <?php if ($film['year']): ?>
                                        <span class="film-year">(<?= $film['year'] ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($film['role'])): ?>
                                <div class="film-role">
                                    als <em><?= htmlspecialchars($film['role']) ?></em>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="film-actions">
                                <button class="btn btn-sm btn-outline-primary edit-role-btn" 
                                        data-film-id="<?= $film['id'] ?>"
                                        data-current-role="<?= htmlspecialchars($film['role'] ?? '') ?>"
                                        title="Rolle bearbeiten">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger remove-film-btn" 
                                        data-film-id="<?= $film['id'] ?>"
                                        title="Film entfernen">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Link zum Profil -->
                <a href="../?page=actor&slug=<?= urlencode($actor['slug']) ?>" 
                   class="btn btn-info w-100 mt-3" 
                   target="_blank">
                    <i class="bi bi-eye"></i> Filmographie ansehen
                </a>
            </div>
            
            <!-- Statistiken -->
            <div class="sidebar-card">
                <h3><i class="bi bi-graph-up"></i> Statistiken</h3>
                <div class="stat-row">
                    <span>Aufrufe:</span>
                    <strong><?= number_format($actor['view_count'] ?? 0) ?></strong>
                </div>
                <?php if (!empty($actor['created_at'])): ?>
                <div class="stat-row">
                    <span>Erstellt:</span>
                    <strong><?= date('d.m.Y', strtotime($actor['created_at'])) ?></strong>
                </div>
                <?php endif; ?>
                <?php if (!empty($actor['updated_at'])): ?>
                <div class="stat-row">
                    <span>Aktualisiert:</span>
                    <strong><?= date('d.m.Y', strtotime($actor['updated_at'])) ?></strong>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Hinweise -->
            <div class="sidebar-card">
                <h3><i class="bi bi-info-circle"></i> Hinweise</h3>
                <ul class="tips-list">
                    <li>Slug wird automatisch aus Namen generiert</li>
                    <li>Foto sollte Seitenverhältnis 2:3 haben (z.B. 400x600px)</li>
                    <li>IMDb ID findest du in der URL: imdb.com/name/<strong>nm0000123</strong></li>
                    <li>Pflichtfelder: Vorname & Nachname</li>
                    <?php if ($isEdit): ?>
                    <li>Filme per Suche hinzufügen oder entfernen</li>
                    <li>Rolle kann nachträglich bearbeitet werden</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Zeichen-Counter für Biografie
const bioTextarea = document.getElementById('bio');
const charCount = document.getElementById('charCount');

function updateCharCount() {
    if (bioTextarea && charCount) {
        charCount.textContent = bioTextarea.value.length;
    }
}

if (bioTextarea) {
    bioTextarea.addEventListener('input', updateCharCount);
    updateCharCount();
}

// Form Validation
const actorForm = document.getElementById('actorForm');
if (actorForm) {
    actorForm.addEventListener('submit', function(e) {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        
        if (!firstName || !lastName) {
            e.preventDefault();
            alert('Bitte Vor- und Nachname eingeben!');
            return false;
        }
    });
}

// Film-Management System
<?php if ($isEdit): ?>
const actorId = <?= $actorId ?>;
let selectedFilmId = null;

// Film-Suche (Autocomplete)
const filmSearchInput = document.getElementById('filmSearchInput');
const filmSearchResults = document.getElementById('filmSearchResults');
const addFilmBtn = document.getElementById('addFilmBtn');
const roleInputSection = document.getElementById('roleInputSection');
const roleInput = document.getElementById('roleInput');

let searchTimeout;

filmSearchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        filmSearchResults.classList.remove('active');
        filmSearchResults.innerHTML = '';
        addFilmBtn.disabled = true;
        roleInputSection.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        searchFilms(query);
    }, 300);
});

async function searchFilms(query) {
    try {
        const response = await fetch(`actions/search-films.php?q=${encodeURIComponent(query)}`);
        const films = await response.json();
        
        if (films.length === 0) {
            filmSearchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--clr-text-muted);">Keine Filme gefunden</div>';
            filmSearchResults.classList.add('active');
            return;
        }
        
        filmSearchResults.innerHTML = films.map(film => `
            <div class="search-result-item" data-film-id="${film.id}" data-film-title="${film.title}">
                <div class="search-result-title">${film.title}</div>
                <div class="search-result-year">${film.year || 'Jahr unbekannt'}</div>
            </div>
        `).join('');
        
        filmSearchResults.classList.add('active');
        
        // Event-Listener für Ergebnisse
        filmSearchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                selectedFilmId = this.dataset.filmId;
                filmSearchInput.value = this.dataset.filmTitle;
                filmSearchResults.classList.remove('active');
                addFilmBtn.disabled = false;
                roleInputSection.style.display = 'block';
                roleInput.focus();
            });
        });
        
    } catch (error) {
        console.error('Search error:', error);
        showNotification('Fehler bei der Suche', 'error');
    }
}

// Film hinzufügen
addFilmBtn.addEventListener('click', async function() {
    if (!selectedFilmId) return;
    
    const role = roleInput.value.trim();
    
    try {
        const response = await fetch('actions/actor-films.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add',
                actor_id: actorId,
                film_id: selectedFilmId,
                role: role
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Film hinzugefügt!', 'success');
            location.reload(); // Seite neu laden
        } else {
            showNotification(result.message || 'Fehler beim Hinzufügen', 'error');
        }
        
    } catch (error) {
        console.error('Add error:', error);
        showNotification('Fehler beim Hinzufügen', 'error');
    }
});

// Film entfernen
document.addEventListener('click', async function(e) {
    if (e.target.closest('.remove-film-btn')) {
        const btn = e.target.closest('.remove-film-btn');
        const filmId = btn.dataset.filmId;
        
        if (!confirm('Film wirklich aus der Filmographie entfernen?')) {
            return;
        }
        
        try {
            const response = await fetch('actions/actor-films.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'remove',
                    actor_id: actorId,
                    film_id: filmId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Film entfernt!', 'success');
                // Film-Item entfernen
                const filmItem = btn.closest('.film-item');
                filmItem.style.opacity = '0';
                setTimeout(() => {
                    filmItem.remove();
                    updateFilmCount();
                }, 300);
            } else {
                showNotification(result.message || 'Fehler beim Entfernen', 'error');
            }
            
        } catch (error) {
            console.error('Remove error:', error);
            showNotification('Fehler beim Entfernen', 'error');
        }
    }
    
    // Rolle bearbeiten
    if (e.target.closest('.edit-role-btn')) {
        const btn = e.target.closest('.edit-role-btn');
        const filmId = btn.dataset.filmId;
        const currentRole = btn.dataset.currentRole;
        
        const newRole = prompt('Rolle / Charakter:', currentRole);
        
        if (newRole === null) return; // Abgebrochen
        
        try {
            const response = await fetch('actions/actor-films.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_role',
                    actor_id: actorId,
                    film_id: filmId,
                    role: newRole.trim()
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Rolle aktualisiert!', 'success');
                // Rolle im UI aktualisieren
                const filmItem = btn.closest('.film-item');
                const roleDiv = filmItem.querySelector('.film-role');
                if (newRole.trim()) {
                    if (roleDiv) {
                        roleDiv.innerHTML = `als <em>${newRole.trim()}</em>`;
                    } else {
                        const roleElement = document.createElement('div');
                        roleElement.className = 'film-role';
                        roleElement.innerHTML = `als <em>${newRole.trim()}</em>`;
                        filmItem.querySelector('.film-info').appendChild(roleElement);
                    }
                } else if (roleDiv) {
                    roleDiv.remove();
                }
                btn.dataset.currentRole = newRole.trim();
            } else {
                showNotification(result.message || 'Fehler beim Aktualisieren', 'error');
            }
            
        } catch (error) {
            console.error('Update error:', error);
            showNotification('Fehler beim Aktualisieren', 'error');
        }
    }
});

// Filmanzahl aktualisieren
function updateFilmCount() {
    const filmsList = document.getElementById('currentFilmsList');
    const count = filmsList.querySelectorAll('.film-item').length;
    document.getElementById('filmCountDisplay').textContent = count;
    
    // "Keine Filme" Nachricht anzeigen/verstecken
    const noFilmsMsg = document.getElementById('noFilmsMessage');
    if (count === 0 && noFilmsMsg) {
        noFilmsMsg.style.display = 'block';
    } else if (noFilmsMsg) {
        noFilmsMsg.style.display = 'none';
    }
}

// Notification Helper
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 250px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
<?php endif; ?>

// ============================================================================
// TMDb Actor Enrichment
// ============================================================================
const loadFromTMDbBtn = document.getElementById('loadFromTMDbBtn');
const csrfToken = '<?= $_SESSION['csrf_token'] ?>';

if (loadFromTMDbBtn) {
    loadFromTMDbBtn.addEventListener('click', async function() {
        const actorName = (document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value).trim();
        
        if (!actorName || actorName.length < 3) {
            alert('Bitte geben Sie zuerst einen Namen ein!');
            return;
        }
        
        // Button disabled während der Suche
        loadFromTMDbBtn.disabled = true;
        loadFromTMDbBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Suche...';
        
        try {
            // Schritt 1: Schauspieler auf TMDb suchen
            const searchResponse = await fetch('actions/tmdb-actor-search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `name=${encodeURIComponent(actorName)}&csrf_token=${encodeURIComponent(csrfToken)}`
            });
            
            const searchData = await searchResponse.json();
            
            if (!searchData.success || !searchData.results || searchData.results.length === 0) {
                alert('Kein Schauspieler auf TMDb gefunden für: ' + actorName);
                loadFromTMDbBtn.disabled = false;
                loadFromTMDbBtn.innerHTML = '<i class="bi bi-cloud-download"></i>';
                return;
            }
            
            // Schritt 2: Wenn mehrere Ergebnisse, zeige Auswahl-Modal
            let selectedTMDbId = null;
            
            if (searchData.results.length === 1) {
                // Nur ein Ergebnis - direkt verwenden
                selectedTMDbId = searchData.results[0].tmdb_id;
            } else {
                // Mehrere Ergebnisse - Benutzer wählen lassen
                selectedTMDbId = await showTMDbSearchResults(searchData.results);
                
                if (!selectedTMDbId) {
                    loadFromTMDbBtn.disabled = false;
                    loadFromTMDbBtn.innerHTML = '<i class="bi bi-cloud-download"></i>';
                    return;
                }
            }
            
            // Schritt 3: Actor-Details laden und Formular füllen
            await enrichActorProfile(selectedTMDbId);
            
        } catch (error) {
            console.error('TMDb Error:', error);
            alert('Fehler beim Laden von TMDb: ' + error.message);
        } finally {
            loadFromTMDbBtn.disabled = false;
            loadFromTMDbBtn.innerHTML = '<i class="bi bi-cloud-download"></i>';
        }
    });
}

async function showTMDbSearchResults(results) {
    return new Promise((resolve) => {
        // Modal erstellen
        const modal = document.createElement('div');
        modal.className = 'tmdb-search-modal';
        modal.innerHTML = `
            <div class="tmdb-modal-overlay"></div>
            <div class="tmdb-modal-content">
                <h3><i class="bi bi-search"></i> TMDb Suchergebnisse</h3>
                <p class="text-muted">Bitte wählen Sie den richtigen Schauspieler:</p>
                <div class="tmdb-results-list">
                    ${results.map(actor => `
                        <div class="tmdb-result-item" data-tmdb-id="${actor.tmdb_id}">
                            ${actor.profile_path ? `
                                <img src="https://image.tmdb.org/t/p/w92${actor.profile_path}" alt="${actor.name}">
                            ` : `
                                <div class="tmdb-no-image"><i class="bi bi-person"></i></div>
                            `}
                            <div class="tmdb-result-info">
                                <div class="tmdb-result-name">${actor.name}</div>
                                <div class="tmdb-result-meta">
                                    ${actor.known_for ? `<small>${actor.known_for}</small>` : ''}
                                    ${actor.popularity ? `<span class="badge bg-secondary">${Math.round(actor.popularity)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button class="btn btn-secondary" id="tmdbCancelBtn">Abbrechen</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event Listeners
        modal.querySelectorAll('.tmdb-result-item').forEach(item => {
            item.addEventListener('click', function() {
                const tmdbId = parseInt(this.dataset.tmdbId);
                modal.remove();
                resolve(tmdbId);
            });
        });
        
        document.getElementById('tmdbCancelBtn').addEventListener('click', () => {
            modal.remove();
            resolve(null);
        });
        
        modal.querySelector('.tmdb-modal-overlay').addEventListener('click', () => {
            modal.remove();
            resolve(null);
        });
    });
}

async function enrichActorProfile(tmdbId) {
    try {
        const response = await fetch('actions/enrich-actor-from-tmdb.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `tmdb_id=${tmdbId}&csrf_token=${encodeURIComponent(csrfToken)}`
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Fehler beim Laden der Daten');
        }
        
        // Formular-Felder füllen
        const actorData = data.data;
        
        // Name aufteilen
        if (actorData.name) {
            const nameParts = actorData.name.split(' ');
            const firstName = nameParts[0] || '';
            const lastName = nameParts.slice(1).join(' ') || '';
            
            document.getElementById('first_name').value = firstName;
            document.getElementById('last_name').value = lastName;
        }
        
        // Biografie
        if (actorData.biography) {
            document.getElementById('bio').value = actorData.biography;
            updateCharCount(); // Zeichen-Counter aktualisieren
        }
        
        // Geburtsdatum
        if (actorData.birth_date) {
            document.getElementById('birth_date').value = actorData.birth_date;
        }
        
        // Geburtsort
        if (actorData.birth_place) {
            document.getElementById('birth_place').value = actorData.birth_place;
        }
        
        // Todesdatum
        if (actorData.death_date) {
            document.getElementById('death_date').value = actorData.death_date;
        }
        
        // TMDb ID
        document.getElementById('tmdb_id').value = tmdbId;
        
        // Profilbild-Vorschau (falls vorhanden)
        if (actorData.profile_image_url) {
            showProfileImagePreview(actorData.profile_image_url);
        }
        
        // Success-Nachricht
        showNotification('✅ Profil erfolgreich von TMDb geladen!', 'success');
        
    } catch (error) {
        console.error('Enrichment error:', error);
        throw error;
    }
}

function showProfileImagePreview(imageUrl) {
    const photoSection = document.querySelector('.form-section:has(#photo)');
    
    // Entferne alte Vorschau falls vorhanden
    const oldPreview = document.getElementById('tmdbPhotoPreview');
    if (oldPreview) {
        oldPreview.remove();
    }
    
    // Neue Vorschau erstellen
    const preview = document.createElement('div');
    preview.id = 'tmdbPhotoPreview';
    preview.className = 'alert alert-success';
    preview.innerHTML = `
        <h5><i class="bi bi-check-circle"></i> Profilbild von TMDb gefunden</h5>
        <img src="${imageUrl}" alt="TMDb Profilbild" style="max-width: 200px; border-radius: 8px; margin-top: 10px;">
        <p class="mt-2 mb-0">
            <small>Dieses Bild wird beim Speichern automatisch heruntergeladen.</small><br>
            <small>Sie können auch ein eigenes Foto hochladen (überschreibt das TMDb-Bild).</small>
        </p>
        <input type="hidden" name="tmdb_profile_image_url" value="${imageUrl}">
    `;
    
    photoSection.insertBefore(preview, photoSection.querySelector('.form-group'));
}

</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* ============================================================================
   TMDb Search Modal Styles
   ============================================================================ */
.tmdb-search-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tmdb-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.tmdb-modal-content {
    position: relative;
    background: var(--clr-card, #2a2a2a);
    border: 1px solid var(--clr-border, rgba(255, 255, 255, 0.1));
    border-radius: var(--radius, 12px);
    padding: 2rem;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.tmdb-modal-content h3 {
    margin: 0 0 1rem 0;
    color: var(--clr-text, #fff);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tmdb-modal-content h3 i {
    color: var(--clr-accent, #667eea);
}

.tmdb-results-list {
    margin: 1.5rem 0;
    max-height: 400px;
    overflow-y: auto;
}

.tmdb-result-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--clr-border, rgba(255, 255, 255, 0.1));
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.tmdb-result-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--clr-accent, #667eea);
    transform: translateX(4px);
}

.tmdb-result-item img {
    width: 60px;
    height: 90px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.tmdb-no-image {
    width: 60px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
    color: var(--clr-text-muted, rgba(255, 255, 255, 0.3));
    font-size: 2rem;
}

.tmdb-result-info {
    flex: 1;
}

.tmdb-result-name {
    font-weight: 600;
    color: var(--clr-text, #fff);
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
}

.tmdb-result-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--clr-text-muted, rgba(255, 255, 255, 0.6));
    font-size: 0.9rem;
}

.tmdb-result-meta small {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#tmdbCancelBtn {
    width: 100%;
    margin-top: 1rem;
}

</style>
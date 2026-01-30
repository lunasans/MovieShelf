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
                            <input 
                                type="number" 
                                id="tmdb_id" 
                                name="tmdb_id" 
                                class="form-control"
                                value="<?= htmlspecialchars($actor['tmdb_id'] ?? '') ?>"
                                placeholder="123456"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
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
        
        <!-- Rechte Spalte: Vorschau & Statistiken -->
        <div class="edit-sidebar">
            <?php if ($isEdit): ?>
            <div class="sidebar-card">
                <h3><i class="bi bi-film"></i> Filmographie</h3>
                <div class="stat-big">
                    <div class="stat-value"><?= $filmCount ?></div>
                    <div class="stat-label">Filme in Sammlung</div>
                </div>
                <a href="../?page=actor&slug=<?= urlencode($actor['slug']) ?>" 
                   class="btn btn-info w-100" 
                   target="_blank">
                    <i class="bi bi-eye"></i> Filmographie ansehen
                </a>
            </div>
            
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
            
            <div class="sidebar-card">
                <h3><i class="bi bi-info-circle"></i> Hinweise</h3>
                <ul class="tips-list">
                    <li>Slug wird automatisch aus Namen generiert</li>
                    <li>Foto sollte Seitenverhältnis 2:3 haben (z.B. 400x600px)</li>
                    <li>IMDb ID findest du in der URL: imdb.com/name/<strong>nm0000123</strong></li>
                    <li>Pflichtfelder: Vorname & Nachname</li>
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
</script>
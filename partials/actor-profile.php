<?php
/**
 * MovieShelf - Schauspieler-Profil Ansicht
 * Zeigt detaillierte Informationen zu einem Schauspieler
 * 
 * @package    movieshelf
 * @version    1.5.0
 * @author     René Neuhaus
 */

// Konstanten für Konfiguration
define('ACTOR_PROFILE_MAX_TOP_GENRES', 5);
define('ACTOR_PROFILE_MAX_FILM_GENRES', 2);
define('ACTOR_PROFILE_DEFAULT_NAME', 'Unbekannter Schauspieler');

// Sicherheitscheck
if (!isset($actor) || !is_array($actor) || empty($actor['id'])) {
    throw new InvalidArgumentException('Invalid actor data provided to actor-profile.php');
}

// Helper-Funktion für Genre-Verarbeitung
if (!function_exists('parseGenres')) {
    function parseGenres(?string $genreString): array {
        if (empty($genreString)) {
            return [];
        }
        return array_map('trim', explode(',', $genreString));
    }
}

// Vollständigen Namen zusammensetzen mit Fallback
$firstName = trim($actor['first_name'] ?? '');
$lastName = trim($actor['last_name'] ?? '');
$fullName = trim($firstName . ' ' . $lastName);

// Fallback wenn Name leer ist
if (empty($fullName)) {
    $fullName = ACTOR_PROFILE_DEFAULT_NAME;
}

// Alter berechnen (falls Geburtsdatum vorhanden)
$age = null;
$ageText = '';
if (!empty($actor['birth_date'])) {
    try {
        $birthDate = new DateTime($actor['birth_date']);
        $today = new DateTime();

        if (!empty($actor['death_date'])) {
            try {
                $deathDate = new DateTime($actor['death_date']);
                $age = $birthDate->diff($deathDate)->y;
                $ageText = "✝ $age Jahre";
            } catch (Exception $e) {
                error_log("Invalid death_date for actor {$actor['id']}: " . $e->getMessage());
                // Nur Geburtsdatum anzeigen, ohne Sterbedatum
                $age = $birthDate->diff($today)->y;
                $ageText = "$age Jahre";
            }
        } else {
            $age = $birthDate->diff($today)->y;
            $ageText = "$age Jahre";
        }
    } catch (Exception $e) {
        error_log("Invalid birth_date for actor {$actor['id']}: " . $e->getMessage());
        // Kein Alter anzeigen bei ungültigem Datum
    }
}

// Filme des Schauspielers laden
$actorFilms = [];
$filmLoadError = null;
try {
    $stmt = $pdo->prepare("
        SELECT 
            d.id, d.title, d.year, d.genre, d.cover_id, d.runtime, 
            d.rating_age,
            fa.role, fa.is_main_role
        FROM dvds d
        INNER JOIN film_actor fa ON d.id = fa.film_id
        WHERE fa.actor_id = ?
        AND d.deleted = 0
        ORDER BY d.year DESC, d.title ASC
    ");
    $stmt->execute([$actor['id']]);
    $actorFilms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Actor films query error: " . $e->getMessage());
    $filmLoadError = "Fehler beim Laden der Filmographie. Bitte versuchen Sie es später erneut.";
}

// Statistiken berechnen
$totalFilms = count($actorFilms);
$mainRoles = array_filter($actorFilms, function($film) {
    return !empty($film['is_main_role']);
});
$mainRoleCount = count($mainRoles);

// Genre-Verteilung mit Helper-Funktion
$genres = [];
foreach ($actorFilms as $film) {
    $filmGenres = parseGenres($film['genre'] ?? null);
    foreach ($filmGenres as $genre) {
        if (!isset($genres[$genre])) {
            $genres[$genre] = 0;
        }
        $genres[$genre]++;
    }
}
arsort($genres);
$topGenres = array_slice($genres, 0, ACTOR_PROFILE_MAX_TOP_GENRES);

// Zeitspanne berechnen
$years = array_filter(array_column($actorFilms, 'year'));
$firstYear = !empty($years) ? min($years) : null;
$lastYear = !empty($years) ? max($years) : null;
$yearSpan = ($firstYear && $lastYear) ? "$firstYear - $lastYear" : null;

// View Counter erhöhen (nur einmal pro Session)
$viewedActorsKey = 'viewed_actors';
if (!isset($_SESSION[$viewedActorsKey])) {
    $_SESSION[$viewedActorsKey] = [];
}

if (!in_array($actor['id'], $_SESSION[$viewedActorsKey], true)) {
    try {
        $pdo->prepare("UPDATE actors SET view_count = view_count + 1 WHERE id = ?")->execute([$actor['id']]);
        $_SESSION[$viewedActorsKey][] = $actor['id'];
    } catch (PDOException $e) {
        error_log("Actor view count update error: " . $e->getMessage());
    }
}

// Foto-Pfad
$actorPhoto = !empty($actor['photo_path']) 
    ? htmlspecialchars($actor['photo_path']) 
    : ACTOR_PLACEHOLDER;

// Meta-Daten für SEO
$pageTitle = $fullName . " - Schauspieler-Profil";
$metaDescription = "Profil von " . $fullName;
if (!empty($actor['bio'])) {
    $metaDescription .= " - " . mb_substr(strip_tags($actor['bio']), 0, 150);
}
?>

<article class="actor-profile" itemscope itemtype="https://schema.org/Person">
    <!-- Header mit Foto und Basis-Info -->
    <header class="actor-header">
        <div class="actor-photo-container">
            <img
                src="<?= $actorPhoto ?>"
                alt="Porträtfoto von <?= htmlspecialchars($fullName) ?>"
                class="actor-photo"
                itemprop="image"
                onerror="this.src='<?= ACTOR_PLACEHOLDER ?>'"
            >
            
            <!-- Social Links -->
            <?php if (!empty($actor['imdb_id']) || !empty($actor['website'])): ?>
            <div class="actor-social-links">
                <?php if (!empty($actor['imdb_id'])): ?>
                <a href="https://www.imdb.com/name/<?= htmlspecialchars($actor['imdb_id']) ?>/"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="social-link imdb"
                   title="IMDb Profil"
                   aria-label="<?= htmlspecialchars($fullName) ?> auf IMDb ansehen">
                    <i class="bi bi-film"></i> IMDb
                </a>
                <?php endif; ?>

                <?php if (!empty($actor['website'])): ?>
                <a href="<?= htmlspecialchars($actor['website']) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="social-link website"
                   title="Offizielle Website"
                   aria-label="Offizielle Website von <?= htmlspecialchars($fullName) ?> besuchen">
                    <i class="bi bi-globe"></i> Website
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="actor-header-info">
            <h1 class="actor-name" itemprop="name"><?= htmlspecialchars($fullName) ?></h1>

            
            <div class="actor-meta" id="actorMetaSection">
                <?php if (!empty($actor['birth_date'])): ?>
                <div class="meta-item">
                    <i class="bi bi-calendar-event"></i>
                    <span itemprop="birthDate" content="<?= $actor['birth_date'] ?>">
                        <?= date('d.m.Y', strtotime($actor['birth_date'])) ?>
                    </span>
                    <?php if ($ageText): ?>
                        <span class="age-badge"><?= $ageText ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($actor['birth_place'])): ?>
                <div class="meta-item">
                    <i class="bi bi-geo-alt"></i>
                    <span itemprop="birthPlace"><?= htmlspecialchars($actor['birth_place']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($actor['nationality'])): ?>
                <div class="meta-item">
                    <i class="bi bi-flag"></i>
                    <span itemprop="nationality"><?= htmlspecialchars($actor['nationality']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistiken -->
            <div class="actor-stats">
                <div class="stat-box" role="group" aria-label="<?= $totalFilms ?> Filme in Sammlung">
                    <div class="stat-value"><?= $totalFilms ?></div>
                    <div class="stat-label">Filme in Sammlung</div>
                </div>

                <?php if ($mainRoleCount > 0): ?>
                <div class="stat-box" role="group" aria-label="<?= $mainRoleCount ?> Hauptrollen">
                    <div class="stat-value"><?= $mainRoleCount ?></div>
                    <div class="stat-label">Hauptrollen</div>
                </div>
                <?php endif; ?>

                <?php if ($yearSpan): ?>
                <div class="stat-box" role="group" aria-label="Zeitspanne <?= htmlspecialchars($yearSpan) ?>">
                    <div class="stat-value"><?= $yearSpan ?></div>
                    <div class="stat-label">Zeitspanne</div>
                </div>
                <?php endif; ?>

                <?php if (!empty($topGenres)): ?>
                <div class="stat-box full-width" role="group" aria-label="Häufigste Genres">
                    <div class="stat-label">Häufigste Genres</div>
                    <div class="genre-tags">
                        <?php foreach ($topGenres as $genre => $count): ?>
                        <span class="genre-tag">
                            <?= htmlspecialchars($genre) ?>
                            <span class="genre-count">(<?= $count ?>)</span>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Biografie -->
    <?php if (!empty($actor['bio'])): ?>
    <section class="actor-section biography">
        <h2><i class="bi bi-journal-text"></i> Biografie</h2>
        <div class="bio-content" itemprop="description">
            <?= nl2br(htmlspecialchars($actor['bio'])) ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Filmographie in dieser Sammlung -->
    <section class="actor-section filmography">
        <h2>
            <i class="bi bi-film"></i> Filmographie in dieser Sammlung
            <span class="section-count"><?= $totalFilms ?> Film<?= $totalFilms !== 1 ? 'e' : '' ?></span>
        </h2>
        
        <?php if ($filmLoadError): ?>
        <div class="error-message" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <p><?= htmlspecialchars($filmLoadError) ?></p>
        </div>
        <?php elseif (empty($actorFilms)): ?>
        <div class="empty-state" role="status" aria-live="polite">
            <i class="bi bi-inbox"></i>
            <p>Keine Filme in der Sammlung mit diesem Schauspieler.</p>
        </div>
        <?php else: ?>
        <div class="filmography-grid">
            <?php foreach ($actorFilms as $film): ?>
            <div class="film-card toggle-detail"
                 data-id="<?= $film['id'] ?>"
                 tabindex="0"
                 role="button"
                 aria-label="Film <?= htmlspecialchars($film['title']) ?> von <?= !empty($film['year']) ? htmlspecialchars($film['year']) : 'unbekannt' ?> anzeigen">
                <div class="film-cover">
                    <?php 
                    $coverPath = findCoverImage($film['cover_id'] ?? '', 'f');
                    if (file_exists($coverPath)):
                    ?>
                        <img src="<?= htmlspecialchars($coverPath) ?>" 
                             alt="Cover von <?= htmlspecialchars($film['title']) ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="cover-placeholder" aria-label="Kein Cover verfügbar">
                            <i class="bi bi-film"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($film['is_main_role'])): ?>
                    <div class="main-role-badge" title="Hauptrolle" aria-label="Hauptrolle">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="film-info">
                    <h3 class="film-title"><?= htmlspecialchars($film['title']) ?></h3>
                    
                    <?php if (!empty($film['year'])): ?>
                    <div class="film-year"><?= htmlspecialchars($film['year']) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($film['role'])): ?>
                    <div class="film-role">
                        als <em><?= htmlspecialchars($film['role']) ?></em>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($film['genre'])): ?>
                    <div class="film-genres">
                        <?php 
                        $filmGenres = array_slice(parseGenres($film['genre']), 0, ACTOR_PROFILE_MAX_FILM_GENRES);
                        foreach ($filmGenres as $genre): 
                        ?>
                        <span class="genre-badge"><?= htmlspecialchars($genre) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- Admin-Bearbeitungs-Button (nur für eingeloggte Admins) -->
    <?php if (isset($_SESSION['user_id'])): ?>

    </div>
    <?php endif; ?>
</article>

<!-- Schema.org JSON-LD -->
<?php
// Build JSON-LD schema properly to avoid trailing comma issues
$personSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $fullName
];

if (!empty($actor['birth_date'])) {
    $personSchema['birthDate'] = $actor['birth_date'];
}

if (!empty($actor['birth_place'])) {
    $personSchema['birthPlace'] = $actor['birth_place'];
}

if (!empty($actor['death_date'])) {
    $personSchema['deathDate'] = $actor['death_date'];
}

if (!empty($actor['nationality'])) {
    $personSchema['nationality'] = $actor['nationality'];
}

if (!empty($actor['bio'])) {
    $personSchema['description'] = mb_substr(strip_tags($actor['bio']), 0, 200);
}

if (!empty($actorPhoto) && $actorPhoto !== ACTOR_PLACEHOLDER) {
    $personSchema['image'] = $actorPhoto;
}

// Sichere URL-Generierung
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
              '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . 
              ($_SERVER['REQUEST_URI'] ?? '');
$personSchema['url'] = filter_var($currentUrl, FILTER_SANITIZE_URL);
?>
<script type="application/ld+json">
<?= json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>


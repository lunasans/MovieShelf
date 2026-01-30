<?php
/**
 * DVD Profiler Liste - Schauspieler-Profil Ansicht
 * 
 * Zeigt detaillierte Informationen über einen Schauspieler
 * sowie alle Filme in der Sammlung mit diesem Schauspieler
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 * @author     René Neuhaus
 */

// Sicherheitscheck
if (!isset($actor) || !is_array($actor) || empty($actor['id'])) {
    throw new InvalidArgumentException('Invalid actor data provided to actor-profile.php');
}

// Vollständigen Namen zusammensetzen
$fullName = trim($actor['first_name'] . ' ' . $actor['last_name']);

// Alter berechnen (falls Geburtsdatum vorhanden)
$age = null;
$ageText = '';
if (!empty($actor['birth_date'])) {
    $birthDate = new DateTime($actor['birth_date']);
    $today = new DateTime();
    
    if (!empty($actor['death_date'])) {
        $deathDate = new DateTime($actor['death_date']);
        $age = $birthDate->diff($deathDate)->y;
        $ageText = "✝ $age Jahre";
    } else {
        $age = $birthDate->diff($today)->y;
        $ageText = "$age Jahre";
    }
}

// Filme des Schauspielers laden
$actorFilms = [];
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
}

// Statistiken berechnen
$totalFilms = count($actorFilms);
$mainRoles = array_filter($actorFilms, function($film) {
    return !empty($film['is_main_role']);
});
$mainRoleCount = count($mainRoles);

// Genre-Verteilung
$genres = [];
foreach ($actorFilms as $film) {
    if (!empty($film['genre'])) {
        $filmGenres = explode(',', $film['genre']);
        foreach ($filmGenres as $genre) {
            $genre = trim($genre);
            if (!isset($genres[$genre])) {
                $genres[$genre] = 0;
            }
            $genres[$genre]++;
        }
    }
}
arsort($genres);
$topGenres = array_slice($genres, 0, 5);

// Zeitspanne berechnen
$years = array_filter(array_column($actorFilms, 'year'));
$firstYear = !empty($years) ? min($years) : null;
$lastYear = !empty($years) ? max($years) : null;
$yearSpan = ($firstYear && $lastYear) ? "$firstYear - $lastYear" : null;

// View Counter erhöhen
try {
    $pdo->prepare("UPDATE actors SET view_count = view_count + 1 WHERE id = ?")->execute([$actor['id']]);
} catch (PDOException $e) {
    error_log("Actor view count update error: " . $e->getMessage());
}

// Foto-Pfad
$actorPhoto = !empty($actor['photo_path']) 
    ? htmlspecialchars($actor['photo_path']) 
    : 'images/placeholder-actor.png';

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
                alt="<?= htmlspecialchars($fullName) ?>"
                class="actor-photo"
                itemprop="image"
                onerror="this.src='images/placeholder-actor.png'"
            >
            
            <!-- Social Links -->
            <?php if (!empty($actor['imdb_id']) || !empty($actor['website'])): ?>
            <div class="actor-social-links">
                <?php if (!empty($actor['imdb_id'])): ?>
                <a href="https://www.imdb.com/name/<?= htmlspecialchars($actor['imdb_id']) ?>/" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="social-link imdb"
                   title="IMDb Profil">
                    <i class="bi bi-film"></i> IMDb
                </a>
                <?php endif; ?>
                
                <?php if (!empty($actor['website'])): ?>
                <a href="<?= htmlspecialchars($actor['website']) ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="social-link website"
                   title="Offizielle Website">
                    <i class="bi bi-globe"></i> Website
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="actor-header-info">
            <h1 class="actor-name" itemprop="name"><?= htmlspecialchars($fullName) ?></h1>
            
            <div class="actor-meta">
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
                <div class="stat-box">
                    <div class="stat-value"><?= $totalFilms ?></div>
                    <div class="stat-label">Filme in Sammlung</div>
                </div>
                
                <?php if ($mainRoleCount > 0): ?>
                <div class="stat-box">
                    <div class="stat-value"><?= $mainRoleCount ?></div>
                    <div class="stat-label">Hauptrollen</div>
                </div>
                <?php endif; ?>
                
                <?php if ($yearSpan): ?>
                <div class="stat-box">
                    <div class="stat-value"><?= $yearSpan ?></div>
                    <div class="stat-label">Zeitspanne</div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($topGenres)): ?>
                <div class="stat-box full-width">
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
        
        <?php if (empty($actorFilms)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Keine Filme in der Sammlung mit diesem Schauspieler.</p>
        </div>
        <?php else: ?>
        <div class="filmography-grid">
            <?php foreach ($actorFilms as $film): ?>
            <div class="film-card" onclick="loadFilmDetails(<?= $film['id'] ?>)">
                <div class="film-cover">
                    <?php 
                    $coverPath = findCoverImage($film['cover_id'] ?? '', 'f');
                    if (file_exists($coverPath)):
                    ?>
                        <img src="<?= htmlspecialchars($coverPath) ?>" 
                             alt="<?= htmlspecialchars($film['title']) ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="cover-placeholder">
                            <i class="bi bi-film"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($film['is_main_role'])): ?>
                    <div class="main-role-badge" title="Hauptrolle">
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
                        $filmGenres = array_slice(explode(',', $film['genre']), 0, 2);
                        foreach ($filmGenres as $genre): 
                        ?>
                        <span class="genre-badge"><?= htmlspecialchars(trim($genre)) ?></span>
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
    <div class="admin-actions">
        <a href="admin/?page=actor-edit&id=<?= $actor['id'] ?>" class="btn btn-admin">
            <i class="bi bi-pencil"></i> Profil bearbeiten
        </a>
    </div>
    <?php endif; ?>
</article>

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "<?= htmlspecialchars($fullName) ?>",
    <?php if (!empty($actor['birth_date'])): ?>
    "birthDate": "<?= $actor['birth_date'] ?>",
    <?php endif; ?>
    <?php if (!empty($actor['birth_place'])): ?>
    "birthPlace": "<?= htmlspecialchars($actor['birth_place']) ?>",
    <?php endif; ?>
    <?php if (!empty($actor['death_date'])): ?>
    "deathDate": "<?= $actor['death_date'] ?>",
    <?php endif; ?>
    <?php if (!empty($actor['nationality'])): ?>
    "nationality": "<?= htmlspecialchars($actor['nationality']) ?>",
    <?php endif; ?>
    <?php if (!empty($actor['bio'])): ?>
    "description": "<?= htmlspecialchars(mb_substr(strip_tags($actor['bio']), 0, 200)) ?>",
    <?php endif; ?>
    <?php if (!empty($actorPhoto)): ?>
    "image": "<?= $actorPhoto ?>",
    <?php endif; ?>
    "url": "<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"
}
</script>

<style>
/* Wird später in separate CSS-Datei verschoben */
.actor-profile {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--space-lg, 1.5rem);
}

.actor-header {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: var(--space-xl, 2rem);
    margin-bottom: var(--space-xl, 2rem);
    padding: var(--space-xl, 2rem);
    background: var(--glass-bg-strong, rgba(255, 255, 255, 0.05));
    border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
}

.actor-photo-container {
    position: relative;
}

.actor-photo {
    width: 100%;
    aspect-ratio: 2/3;
    object-fit: cover;
    border-radius: var(--radius-md, 8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.actor-social-links {
    display: flex;
    gap: var(--space-sm, 0.5rem);
    margin-top: var(--space-md, 1rem);
}

.social-link {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-xs, 0.35rem);
    padding: var(--space-sm, 0.5rem);
    background: var(--accent-primary, #667eea);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-sm, 6px);
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--accent-hover, #764ba2);
    transform: translateY(-2px);
}

.actor-name {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-white, #ffffff);
    margin: 0 0 var(--space-md, 1rem) 0;
}

.actor-meta {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm, 0.5rem);
    margin-bottom: var(--space-lg, 1.5rem);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm, 0.5rem);
    color: var(--text-glass, rgba(255, 255, 255, 0.8));
    font-size: 1rem;
}

.meta-item i {
    color: var(--accent-primary, #667eea);
    font-size: 1.2rem;
}

.age-badge {
    padding: 2px 8px;
    background: var(--accent-primary, #667eea);
    border-radius: 4px;
    font-size: 0.85rem;
    margin-left: var(--space-xs, 0.35rem);
}

.actor-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--space-md, 1rem);
    margin-top: var(--space-lg, 1.5rem);
}

.stat-box {
    background: var(--bg-tertiary, rgba(255, 255, 255, 0.03));
    padding: var(--space-md, 1rem);
    border-radius: var(--radius-md, 8px);
    text-align: center;
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.05));
}

.stat-box.full-width {
    grid-column: 1 / -1;
    text-align: left;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent-primary, #667eea);
    margin-bottom: var(--space-xs, 0.35rem);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-muted, rgba(255, 255, 255, 0.6));
}

.genre-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs, 0.35rem);
    margin-top: var(--space-sm, 0.5rem);
}

.genre-tag {
    padding: 4px 12px;
    background: var(--accent-primary, #667eea);
    color: white;
    border-radius: 16px;
    font-size: 0.85rem;
}

.genre-count {
    opacity: 0.8;
}

.actor-section {
    margin-bottom: var(--space-xl, 2rem);
    padding: var(--space-xl, 2rem);
    background: var(--glass-bg, rgba(255, 255, 255, 0.03));
    border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
}

.actor-section h2 {
    display: flex;
    align-items: center;
    gap: var(--space-sm, 0.5rem);
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--text-white, #ffffff);
    margin: 0 0 var(--space-lg, 1.5rem) 0;
}

.actor-section h2 i {
    color: var(--accent-primary, #667eea);
}

.section-count {
    font-size: 1rem;
    color: var(--text-muted, rgba(255, 255, 255, 0.6));
    font-weight: 400;
    margin-left: auto;
}

.bio-content {
    line-height: 1.8;
    color: var(--text-glass, rgba(255, 255, 255, 0.9));
    font-size: 1.05rem;
}

.filmography-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--space-lg, 1.5rem);
}

.film-card {
    background: var(--bg-tertiary, rgba(255, 255, 255, 0.03));
    border-radius: var(--radius-md, 8px);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.05));
}

.film-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    border-color: var(--accent-primary, #667eea);
}

.film-cover {
    position: relative;
    width: 100%;
    aspect-ratio: 2/3;
    overflow: hidden;
}

.film-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cover-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary, rgba(255, 255, 255, 0.05));
    font-size: 3rem;
    color: var(--text-muted, rgba(255, 255, 255, 0.3));
}

.main-role-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255, 215, 0, 0.9);
    color: #000;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
}

.film-info {
    padding: var(--space-md, 1rem);
}

.film-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-white, #ffffff);
    margin: 0 0 var(--space-xs, 0.35rem) 0;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.film-year {
    color: var(--text-muted, rgba(255, 255, 255, 0.6));
    font-size: 0.9rem;
    margin-bottom: var(--space-xs, 0.35rem);
}

.film-role {
    font-size: 0.85rem;
    color: var(--text-glass, rgba(255, 255, 255, 0.7));
    margin-bottom: var(--space-sm, 0.5rem);
}

.film-role em {
    color: var(--accent-primary, #667eea);
}

.film-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.genre-badge {
    padding: 2px 8px;
    background: var(--bg-secondary, rgba(255, 255, 255, 0.05));
    border-radius: 4px;
    font-size: 0.75rem;
    color: var(--text-muted, rgba(255, 255, 255, 0.6));
}

.empty-state {
    text-align: center;
    padding: var(--space-2xl, 3rem);
    color: var(--text-muted, rgba(255, 255, 255, 0.5));
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: var(--space-md, 1rem);
    opacity: 0.3;
}

.admin-actions {
    text-align: center;
    margin-top: var(--space-xl, 2rem);
}

.btn-admin {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm, 0.5rem);
    padding: var(--space-md, 1rem) var(--space-xl, 2rem);
    background: var(--accent-primary, #667eea);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-md, 8px);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-admin:hover {
    background: var(--accent-hover, #764ba2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Responsive */
@media (max-width: 992px) {
    .actor-header {
        grid-template-columns: 1fr;
    }
    
    .actor-photo-container {
        max-width: 300px;
        margin: 0 auto;
    }
}

@media (max-width: 576px) {
    .actor-name {
        font-size: 2rem;
    }
    
    .actor-stats {
        grid-template-columns: 1fr;
    }
    
    .filmography-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: var(--space-md, 1rem);
    }
}
</style>
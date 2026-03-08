<?php
// Bootstrap sollte bereits in film-fragment.php geladen sein
// require_once __DIR__ . '/../includes/bootstrap.php';

// Sicherheitscheck - Film-Array validieren
if (!isset($dvd) || !is_array($dvd) || empty($dvd['id'])) {
    throw new InvalidArgumentException('Invalid DVD data provided to film-view.php');
}

// Cover-Pfade generieren
$frontCover = findCoverImage($dvd['cover_id'] ?? '', 'f');
$backCover = findCoverImage($dvd['cover_id'] ?? '', 'b');

// Schauspieler laden
$actors = getActorsByDvdId($pdo, (int)$dvd['id']);

// Crew-Mitglieder laden (Regie, Drehbuch, etc.)
$crew = null;
if (getSetting('tmdb_api_key', '')) {
    $crew = getFilmCrew($dvd['title'], $dvd['year'] ?? null);
}

// Staffeln & Episoden laden (für Serien)
$seasons = [];
$totalEpisodes = 0;
try {
    // Prüfen ob seasons Tabelle existiert
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'seasons'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        // Staffeln laden
        $seasonsStmt = $pdo->prepare("
            SELECT id, season_number, name, overview, episode_count, air_date, poster_path
            FROM seasons
            WHERE series_id = ?
            ORDER BY season_number ASC
        ");
        $seasonsStmt->execute([$dvd['id']]);
        $seasonsData = $seasonsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($seasonsData)) {
            // Für jede Staffel die Episoden laden
            foreach ($seasonsData as $season) {
                $episodesStmt = $pdo->prepare("
                    SELECT id, episode_number, title, overview, air_date, runtime, still_path
                    FROM episodes
                    WHERE season_id = ?
                    ORDER BY episode_number ASC
                ");
                $episodesStmt->execute([$season['id']]);
                $episodes = $episodesStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $season['episodes'] = $episodes;
                $seasons[] = $season;
                $totalEpisodes += count($episodes);
            }
        }
    }
} catch (PDOException $e) {
    error_log("Seasons/Episodes query error: " . $e->getMessage());
}


// BoxSet-Kinder laden
$boxChildren = [];
if (!empty($dvd['id'])) {
    try {
        $boxsetStmt = $pdo->prepare("
            SELECT id, title, year, genre, cover_id, runtime, rating_age 
            FROM dvds 
            WHERE boxset_parent = ? 
            ORDER BY year ASC, title ASC
        ");
        $boxsetStmt->execute([$dvd['id']]);
        $boxChildren = $boxsetStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("BoxSet query error: " . $e->getMessage());
    }
}

// BoxSet-Parent laden (falls dieser Film zu einem BoxSet gehört)
$boxsetParent = null;
if (!empty($dvd['boxset_parent'])) {
    try {
        $parentStmt = $pdo->prepare("SELECT id, title, year FROM dvds WHERE id = ?");
        $parentStmt->execute([$dvd['boxset_parent']]);
        $boxsetParent = $parentStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("BoxSet parent query error: " . $e->getMessage());
    }
}

// Film-Statistiken laden
$filmStats = [
    'view_count' => $dvd['view_count'] ?? 0,
    'created_at' => $dvd['created_at'] ?? null,
    'updated_at' => $dvd['updated_at'] ?? null,
];

// Bewertung berechnen (falls vorhanden) - Robuster mit Tabellen-Check
$averageRating = 0;
$ratingCount = 0;
$userRating = 0;
$userHasRated = false;

try {
    // Prüfen ob user_ratings Tabelle existiert
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'user_ratings'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        // Allgemeine Bewertungen laden
        $ratingStmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as count 
            FROM user_ratings 
            WHERE film_id = ?
        ");
        $ratingStmt->execute([$dvd['id']]);
        $ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);
        $averageRating = round((float)($ratingData['avg_rating'] ?? 0), 1);
        $ratingCount = (int)($ratingData['count'] ?? 0);
        
        // User-spezifische Bewertung laden (falls eingeloggt)
        if (isset($_SESSION['user_id'])) {
            $userRatingStmt = $pdo->prepare("
                SELECT rating FROM user_ratings 
                WHERE film_id = ? AND user_id = ?
            ");
            $userRatingStmt->execute([$dvd['id'], $_SESSION['user_id']]);
            $userRatingData = $userRatingStmt->fetch();
            if ($userRatingData) {
                $userRating = (float)$userRatingData['rating'];
                $userHasRated = true;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Rating query error: " . $e->getMessage());
}

// User-Status laden (Wishlist, Watched) falls eingeloggt
$isOnWishlist = false;
$isWatched = false;
if (isset($_SESSION['user_id'])) {
    try {
        // Wishlist-Status
        $wishCheck = $pdo->query("SHOW TABLES LIKE 'user_wishlist'");
        if ($wishCheck && $wishCheck->rowCount() > 0) {
            $wishStmt = $pdo->prepare("SELECT COUNT(*) FROM user_wishlist WHERE user_id = ? AND film_id = ?");
            $wishStmt->execute([$_SESSION['user_id'], $dvd['id']]);
            $isOnWishlist = $wishStmt->fetchColumn() > 0;
        }
        
        // Watched-Status
        $watchedCheck = $pdo->query("SHOW TABLES LIKE 'user_watched'");
        if ($watchedCheck && $watchedCheck->rowCount() > 0) {
            $watchedStmt = $pdo->prepare("SELECT COUNT(*) FROM user_watched WHERE user_id = ? AND film_id = ?");
            $watchedStmt->execute([$_SESSION['user_id'], $dvd['id']]);
            $isWatched = $watchedStmt->fetchColumn() > 0;
        }
    } catch (PDOException $e) {
        error_log("User status query error: " . $e->getMessage());
    }
}

// View-Count erhöhen
try {
    if (!empty($dvd['id'])) {
        $updateViewStmt = $pdo->prepare("UPDATE dvds SET view_count = COALESCE(view_count, 0) + 1 WHERE id = ?");
        $updateViewStmt->execute([$dvd['id']]);
        $filmStats['view_count']++; // Lokale Variable aktualisieren
    }
} catch (PDOException $e) {
    error_log("View count update error: " . $e->getMessage());
}

// Helper-Funktionen
function formatFileSize(?int $bytes): string {
    if (!$bytes) return '';
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen((string)$bytes) - 1) / 3);
    return sprintf("%.1f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}

function formatDate(?string $date): string {
    if (!$date) return '';
    try {
        return (new DateTime($date))->format('d.m.Y');
    } catch (Exception $e) {
        return $date;
    }
}

function generateStarRating(float $rating, int $maxStars = 5): string {
    $stars = '';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="bi bi-star-fill star-filled"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $stars .= '<i class="bi bi-star-half star-half"></i>';
        } else {
            $stars .= '<i class="bi bi-star star-empty"></i>';
        }
    }
    return $stars;
}
?>

<?php
// Backcover als Backdrop verwenden
$backdropUrl = $backCover ?? '';
?>



<div class="detail-inline" itemscope itemtype="https://schema.org/Movie">
    <!-- Hero Wrapper - für Backdrop-Begrenzung -->
    <div class="hero-wrapper">
        <!-- Backdrop Hintergrund -->
        <?php if ($backdropUrl): ?>
        <div class="backdrop-container">
            <div class="backdrop-image" style="background-image: url('<?= htmlspecialchars($backdropUrl) ?>');"></div>
            <div class="backdrop-overlay"></div>
        </div>
        <?php endif; ?>
        
        <!-- Hero Section - Cover + Film-Infos nebeneinander (TMDb-Style) -->
        <section class="hero-section">
        <!-- Cover links -->
        <div class="hero-cover">
            <?php if ($frontCover): ?>
                <a href="<?= htmlspecialchars($frontCover) ?>" 
                   data-fancybox="gallery" 
                   data-caption="<?= htmlspecialchars($dvd['title']) ?> - Frontcover">
                    <img src="<?= htmlspecialchars($frontCover) ?>" 
                         alt="<?= htmlspecialchars($dvd['title']) ?> Frontcover"
                         itemprop="image"
                         loading="lazy">
                </a>
            <?php else: ?>
                <div class="no-cover">
                    <i class="bi bi-film"></i>
                    <span>Kein Cover</span>
                </div>
            <?php endif; ?>
            
            <!-- Crew-Informationen (Regie, Drehbuch, etc.) -->
            <?php if ($crew): ?>
            <div class="crew-info">
                <?php if (!empty($crew['director'])): ?>
                <div class="crew-item">
                    <span class="crew-role">Regie</span>
                    <span class="crew-name"><?= htmlspecialchars($crew['director']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($crew['writers'])): ?>
                <div class="crew-item">
                    <span class="crew-role">Drehbuch</span>
                    <span class="crew-name"><?= htmlspecialchars(implode(', ', $crew['writers'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($crew['composer'])): ?>
                <div class="crew-item">
                    <span class="crew-role">Musik</span>
                    <span class="crew-name"><?= htmlspecialchars($crew['composer']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Film-Infos rechts -->
        <div class="hero-content">
            <!-- Film-Titel -->
            <header class="film-header">
                <h2 itemprop="name">
                    <?= htmlspecialchars($dvd['title']) ?>
                    <span class="film-year" itemprop="datePublished">(<?= htmlspecialchars((string)($dvd['year'] ?? '')) ?>)</span>
                </h2>
                
                <!-- User-Status Badges (nur Gesehen) -->
                <?php if (isset($_SESSION['user_id']) && $isWatched): ?>
                    <div class="user-status-badges">
                        <span class="badge badge-watched">
                            <i class="bi bi-check-circle-fill"></i> Gesehen
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($boxsetParent): ?>
                    <div class="boxset-breadcrumb">
                        <i class="bi bi-collection"></i>
                        Teil von: 
                        <a href="#" class="toggle-detail" data-id="<?= $boxsetParent['id'] ?>">
                            <?= htmlspecialchars($boxsetParent['title']) ?> (<?= $boxsetParent['year'] ?>)
                        </a>
                    </div>
                <?php endif; ?>
            </header>
            
            <!-- Meta-Infos in einer Zeile -->
            <div class="hero-meta-line">
                <?php if (isset($dvd['rating_age']) && $dvd['rating_age'] !== null && $dvd['rating_age'] !== ''): ?>
                    <span class="fsk-badge">
                        <?php
                        $fskAge = (int)$dvd['rating_age'];
                        $fskSvgPath = SVG_PATH . "/fsk/fsk-{$fskAge}.svg";
                        
                        if (file_exists(BASE_PATH . '/' . $fskSvgPath)): ?>
                            <img src="<?= htmlspecialchars($fskSvgPath) ?>" 
                                 alt="FSK <?= $fskAge ?>" 
                                 class="fsk-logo"
                                 title="Freigegeben ab <?= $fskAge ?> Jahren">
                        <?php else: ?>
                            <span class="fsk-text">FSK <?= $fskAge ?></span>
                        <?php endif; ?>
                    </span>
                    <span>•</span>
                <?php endif; ?>
                
                <?php if (!empty($dvd['genre'])): ?>
                    <span itemprop="genre"><?= htmlspecialchars($dvd['genre']) ?></span>
                    <span>•</span>
                <?php endif; ?>
                
                <?php if (!empty($dvd['runtime'])): ?>
                    <span itemprop="duration"><?= formatRuntime((int)$dvd['runtime']) ?></span>
                <?php endif; ?>
                
                <?php if ($filmStats['created_at']): ?>
                    <span>•</span>
                    <span title="Hinzugefügt am">
                        <i class="bi bi-calendar-plus"></i> <?= formatDate($filmStats['created_at']) ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- TMDb Ratings -->
            <?php 
            if (getSetting('tmdb_show_ratings_details', '1') == '1') {
                $film = $dvd;
                include __DIR__ . '/rating-details.php';
            }
            ?>
            
            <!-- Handlung -->
            <?php if (!empty($dvd['overview'])): ?>
                <div class="hero-overview">
                    <h3 style="margin-bottom: 0.75rem; font-size: 1.2rem;">Handlung</h3>
                    <div itemprop="description">
                        <?php
                        if (function_exists('purifyHTML')) {
                            echo purifyHTML($dvd['overview'], true);
                        } else {
                            echo nl2br(htmlspecialchars($dvd['overview'], ENT_QUOTES, 'UTF-8'));
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    </div><!-- Ende hero-wrapper -->

    <!-- Schauspieler -->
    <?php if (!empty($actors)): ?>
        <section class="meta-card">
            <h3><i class="bi bi-people"></i> Besetzung</h3>
            <div class="actor-list">
                <ul itemprop="actor" itemscope itemtype="https://schema.org/Person">
                    <?php foreach ($actors as $actor): ?>
                        <li class="actor-item">
                            <?php 
                            // Slug ermitteln mit mehreren Fallbacks
                            $actorSlug = '';
                            if (!empty($actor['slug'])) {
                                // Bevorzugt: Slug aus Datenbank
                                $actorSlug = $actor['slug'];
                            } elseif (!empty($actor['first_name']) && !empty($actor['last_name'])) {
                                // Fallback 1: Slug aus Namen generieren
                                $fullName = $actor['first_name'] . '-' . $actor['last_name'];
                                $actorSlug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', 
                                    str_replace([' ', 'ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'], 
                                               ['-', 'ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue'], 
                                               $fullName)));
                            } elseif (!empty($actor['id'])) {
                                // Fallback 2: Actor ID verwenden
                                $actorSlug = 'actor-' . $actor['id'];
                            }
                            
                            $actorFullName = trim(($actor['first_name'] ?? '') . ' ' . ($actor['last_name'] ?? ''));
                            ?>
                            <?php if (!empty($actorSlug)): ?>
                            <a href="#" 
                               class="actor-name actor-link" 
                               data-actor-slug="<?= htmlspecialchars($actorSlug) ?>"
                               itemprop="name"
                               title="Profil von <?= htmlspecialchars($actorFullName) ?> anzeigen">
                                <?= htmlspecialchars($actorFullName) ?>
                            </a>
                            <?php else: ?>
                            <span class="actor-name"><?= htmlspecialchars($actorFullName) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($actor['role'])): ?>
                                <span class="actor-role">als <em><?= htmlspecialchars($actor['role']) ?></em></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    <!-- Staffeln & Episoden (für Serien) -->
    <?php if (!empty($seasons)): ?>
        <section class="meta-card">
            <h3>
                <i class="bi bi-collection-play"></i> 
                Staffeln & Episoden
                <span class="badge bg-primary ms-2"><?= count($seasons) ?> Staffel<?= count($seasons) > 1 ? 'n' : '' ?></span>
                <span class="badge bg-secondary ms-1"><?= $totalEpisodes ?> Episoden</span>
            </h3>
            
            <div class="seasons-accordion">
                <?php foreach ($seasons as $sIndex => $season): ?>
                <div class="season-section">
                    <div class="season-header" data-season="<?= $season['season_number'] ?>">
                        <div class="season-title">
                            <i class="bi bi-caret-right-fill season-caret" data-caret="<?= $season['season_number'] ?>"></i>
                            <strong>Staffel <?= $season['season_number'] ?></strong>
                            <span class="text-muted ms-2">(<?= count($season['episodes']) ?> Episoden)</span>
                        </div>
                        <?php if (!empty($season['air_date'])): ?>
                        <small class="text-muted"><?= date('Y', strtotime($season['air_date'])) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="season-content" data-content="<?= $season['season_number'] ?>" style="display: <?= $sIndex === 0 ? 'block' : 'none' ?>">
                        <?php if (!empty($season['overview'])): ?>
                        <div class="season-overview">
                            <p class="text-muted small"><?= htmlspecialchars($season['overview']) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="episodes-list">
                            <?php foreach ($season['episodes'] as $episode): ?>
                            <div class="episode-item">
                                <div class="episode-header">
                                    <span class="episode-badge">E<?= $episode['episode_number'] ?></span>
                                    <strong class="episode-title"><?= htmlspecialchars($episode['title']) ?></strong>
                                    <?php if (!empty($episode['runtime'])): ?>
                                    <span class="episode-runtime text-muted"><?= $episode['runtime'] ?> Min.</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($episode['overview'])): ?>
                                <div class="episode-overview">
                                    <p class="text-muted small"><?= htmlspecialchars($episode['overview']) ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($episode['air_date'])): ?>
                                <div class="episode-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?= date('d.m.Y', strtotime($episode['air_date'])) ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
    <?php endif; ?>

    <!-- BoxSet-Inhalte -->
    <?php if (!empty($boxChildren)): ?>
        <section class="meta-card">
            <h3><i class="bi bi-collection"></i> BoxSet-Inhalte</h3>
            <div class="boxset-children-grid">
                <?php foreach ($boxChildren as $child): ?>
                    <div class="boxset-child-item">
                        <a href="#" class="toggle-detail" data-id="<?= $child['id'] ?>">
                            <img src="<?= htmlspecialchars(findCoverImage($child['cover_id'], 'f')) ?>" 
                                 alt="<?= htmlspecialchars($child['title']) ?>"
                                 class="child-cover"
                                 loading="lazy">
                            <div class="child-info">
                                <h4><?= htmlspecialchars($child['title']) ?></h4>
                                <p><?= htmlspecialchars((string)$child['year']) ?></p>
                                <?php if (!empty($child['runtime'])): ?>
                                    <p class="runtime"><?= formatRuntime((int)$child['runtime']) ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Trailer -->
    <?php if (!empty($dvd['trailer_url'])): ?>
        <section class="meta-card">
            <h3><i class="bi bi-play-circle"></i> Trailer</h3>
            <div class="trailer-container">
                <div class="trailer-box" 
                     data-src="<?= htmlspecialchars($dvd['trailer_url']) ?>"
                     data-rating-age="<?= (int)($dvd['rating_age'] ?? 0) ?>">
                    <img src="<?= htmlspecialchars($frontCover) ?>" 
                         alt="Trailer Thumbnail"
                         loading="lazy">
                    <div class="play-icon">
                        <i class="bi bi-play-fill"></i>
                    </div>
                    <div class="trailer-overlay">
                        <span>Trailer abspielen</span>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Age Verification Modal für FSK 18+ -->
    <?php if (!empty($dvd['trailer_url']) && (int)($dvd['rating_age'] ?? 0) >= 18): ?>
    <div id="ageVerificationModal" class="age-modal" style="display: none;">
        <div class="age-modal-content">
            <div class="age-modal-header">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <h3>Altersbeschränkung</h3>
            </div>
            <div class="age-modal-body">
                <p class="age-warning">
                    Dieser Film ist <strong>FSK <?= (int)$dvd['rating_age'] ?></strong> eingestuft.
                </p>
                <p>
                    Der Trailer enthält möglicherweise Inhalte, die für Personen unter <?= (int)$dvd['rating_age'] ?> Jahren nicht geeignet sind.
                </p>
                <p class="age-question">
                    <strong>Bist du mindestens <?= (int)$dvd['rating_age'] ?> Jahre alt?</strong>
                </p>
            </div>
            <div class="age-modal-actions">
                <button class="btn btn-success age-confirm" id="ageConfirmBtn">
                    <i class="bi bi-check-circle"></i> Ja, ich bin <?= (int)$dvd['rating_age'] ?>+
                </button>
                <button class="btn btn-danger age-deny" id="ageDenyBtn">
                    <i class="bi bi-x-circle"></i> Nein, abbrechen
                </button>
            </div>
            <p class="age-disclaimer">
                <small>
                    <i class="bi bi-info-circle"></i>
                    Mit der Bestätigung erklärst du, dass du das gesetzliche Mindestalter erreicht hast.
                </small>
            </p>
        </div>
    </div>
    <?php endif; ?>



    <!-- User-Bewertung (falls eingeloggt) -->
    <section class="meta-card user-rating-card">
        <h3><i class="bi bi-star-fill"></i> Ihre Bewertung</h3>
        <div class="user-rating-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="rating-grid">
                    <!-- Eigene Bewertung -->
                    <div class="rating-card user">
                        <div class="rating-logo">
                            <i class="bi bi-person-circle" style="font-size: 32px; color: var(--accent-primary);"></i>
                        </div>
                        
                        <?php if ($userHasRated): ?>
                            <div class="rating-score" style="color: var(--accent-primary);">
                                <?= $userRating ?><span class="rating-max">/5</span>
                            </div>
                            <div class="stars-display">
                                <?= generateStarRating($userRating) ?>
                            </div>
                            <div class="rating-meta">
                                Ihre Bewertung
                            </div>
                        <?php else: ?>
                            <div class="rating-score" style="color: var(--text-muted); font-size: 1.5rem;">
                                -<span class="rating-max">/5</span>
                            </div>
                            <div class="rating-meta">
                                Noch nicht bewertet
                            </div>
                        <?php endif; ?>
                        
                        <!-- Bewertungs-Input -->
                        <div class="star-rating-input" data-film-id="<?= $dvd['id'] ?>" data-current-rating="<?= $userRating ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi rating-star <?= $i <= $userRating ? 'bi-star-fill' : 'bi-star' ?>" 
                                   data-rating="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        
                        <button class="btn-rate save-rating" style="display: none;">
                            <i class="bi bi-check-circle"></i> Speichern
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="login-required">
                    <i class="bi bi-info-circle"></i>
                    <p>Melden Sie sich an, um Filme zu bewerten.</p>
                    <a href="admin/login.php" class="btn-login">
                        <i class="bi bi-person"></i> Anmelden
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ähnliche Filme -->
    <?php 
    $film = $dvd; // similar-movies.php erwartet $film Variable
    include __DIR__ . '/similar-movies.php';
    ?>

    <!-- Film-Aktionen -->
    <section class="film-actions">
        <button class="action-btn action-close close-detail-button">
            <i class="bi bi-x-lg"></i>
            <span>Schließen</span>
        </button>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="action-btn action-watched mark-as-watched <?= $isWatched ? 'active' : '' ?>" 
                    data-film-id="<?= $dvd['id'] ?>">
                <i class="bi bi-check-circle<?= $isWatched ? '-fill' : '' ?>"></i>
                <span><?= $isWatched ? 'Gesehen' : 'Als gesehen markieren' ?></span>
            </button>
        <?php endif; ?>
        
        <button class="action-btn action-share share-film" 
                data-film-id="<?= $dvd['id'] ?>" 
                data-film-title="<?= htmlspecialchars($dvd['title']) ?>">
            <i class="bi bi-share"></i>
            <span>Teilen</span>
        </button>
    </section>
</div>

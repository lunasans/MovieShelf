<!-- partials/rating-details.php -->
<?php
/**
 * Ausführliche Rating-Anzeige für Film-Detail-Seite
 * Zeigt TMDb + IMDb Ratings mit schönem Design
 * 
 * Usage: include mit $film Array
 */

if (!isset($film)) return;

// Ratings holen
$ratings = getFilmRatings($film['title'], $film['year'] ?? null);

// Wenn keine Ratings, nichts anzeigen
if (!$ratings) {
    return;
}
?>

<div class="ratings-container">
    <h3 class="ratings-title">
        <i class="bi bi-star-fill"></i>
        Bewertungen
    </h3>
    
    <div class="ratings-grid">
        <!-- TMDb Rating -->
        <?php if (!empty($ratings['tmdb_rating']) && $ratings['tmdb_rating'] > 0): ?>
        <div class="rating-card tmdb">
            <div class="rating-logo">
                <img src="https://www.themoviedb.org/assets/2/v4/logos/v2/blue_short-8e7b30f73a4020692ccca9c88bafe5dcb6f8a62a4c6bc55cd9ba82bb2cd95f6c.svg" 
                     alt="TMDb" 
                     style="height: 24px;">
            </div>
            <div class="rating-score" style="color: <?= TMDbHelper::getRatingColor($ratings['tmdb_rating']) ?>;">
                <?= number_format($ratings['tmdb_rating'], 1) ?><span class="rating-max">/10</span>
            </div>
            <div class="rating-votes">
                <?= TMDbHelper::formatRating($ratings['tmdb_rating'], $ratings['tmdb_votes']) ?>
            </div>
            <?php if (!empty($ratings['tmdb_popularity'])): ?>
            <div class="rating-meta">
                Popularität: <?= $ratings['tmdb_popularity'] ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- External Links - Logo-Leiste -->
    <div class="external-links">
        <?php if (!empty($ratings['imdb_id'])): ?>
        <a href="https://www.imdb.com/title/<?= htmlspecialchars($ratings['imdb_id']) ?>/" 
           target="_blank" 
           rel="noopener"
           class="external-logo-link"
           title="Auf IMDb ansehen">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Cpath fill='%23f5c518' d='M0 0h48v48H0z'/%3E%3Cpath d='M9 13h4v22H9zm6 0h4l2 8 2-8h4v22h-3V20l-2 7h-2l-2-7v15h-3zm17 0h8v3h-5v5h5v3h-5v8h5v3h-8z'/%3E%3C/svg%3E" 
                 alt="IMDb">
        </a>
        <?php endif; ?>
        
        <?php if (!empty($ratings['tmdb_rating'])): ?>
        <a href="https://www.themoviedb.org/search?query=<?= urlencode($film['title']) ?>" 
           target="_blank" 
           rel="noopener"
           class="external-logo-link"
           title="Auf TMDb ansehen">
            <img src="https://www.themoviedb.org/assets/2/v4/logos/v2/blue_square_2-d537fb228cf3ded904ef09b136fe3fec72548ebc1fea3fbbd1ad9e36364db38b.svg" 
                 alt="TMDb">
        </a>
        <?php endif; ?>
    </div>
</div>
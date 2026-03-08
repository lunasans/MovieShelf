<?php
/**
 * similar-movies.php
 * Zeigt ähnliche Filme basierend auf TMDb
 * 
 * Variablen die gesetzt sein müssen:
 * $film - Array mit Film-Daten (title, year)
 */

// TMDb aktiviert?
if (getSetting('tmdb_show_similar_movies', '1') != '1' || empty(getSetting('tmdb_api_key', ''))) {
    return;
}

// TMDb Helper initialisieren
$apiKey = getSetting('tmdb_api_key', '');
$tmdb = new TMDbHelper($apiKey);

// Ähnliche Filme laden
$similarMovies = $tmdb->getSimilarMovies($film['title'], $film['year'] ?? null, 8);

if (empty($similarMovies)) {
    return;
}
?>

<section class="similar-movies-section">
    <h3>
        <i class="bi bi-film"></i>
        Das könnte dir auch gefallen
    </h3>
    
    <div class="similar-movies-grid">
        <?php foreach ($similarMovies as $movie): ?>
            <div class="similar-movie-card">
                <?php if ($movie['poster_path']): ?>
                    <img src="https://image.tmdb.org/t/p/w185<?= htmlspecialchars($movie['poster_path']) ?>" 
                         alt="<?= htmlspecialchars($movie['title']) ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="no-poster">
                        <i class="bi bi-film"></i>
                    </div>
                <?php endif; ?>
                
                <div class="similar-movie-info">
                    <h4><?= htmlspecialchars($movie['title']) ?></h4>
                    
                    <div class="similar-movie-meta">
                        <?php if ($movie['year']): ?>
                            <span class="year"><?= $movie['year'] ?></span>
                        <?php endif; ?>
                        
                        <?php if ($movie['rating'] > 0): ?>
                            <span class="rating">
                                <i class="bi bi-star-fill"></i>
                                <?= number_format($movie['rating'], 1) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($movie['overview'])): ?>
                        <p class="overview"><?= htmlspecialchars(mb_substr($movie['overview'], 0, 100)) ?>...</p>
                    <?php endif; ?>
                    
                    <a href="https://www.themoviedb.org/movie/<?= $movie['tmdb_id'] ?>" 
                       target="_blank" 
                       rel="noopener"
                       class="btn-tmdb-link">
                        Auf TMDb ansehen <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
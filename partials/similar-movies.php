<?php
/**
 * similar-movies.php
 * Zeigt ähnliche Filme aus der eigenen Sammlung basierend auf Genres
 * 
 * Variablen die gesetzt sein müssen:
 * $film - Array mit Film-Daten (id, genre)
 */

if (empty($film['id'])) {
    return;
}

// Genres extrahieren und säubern
$genres = [];
if (!empty($film['genre'])) {
    // Teilt bei Komma, Semicolon oder Slash
    $rawGenres = preg_split('/[,;\/]/', $film['genre']);
    foreach ($rawGenres as $g) {
        $trimmed = trim($g);
        if (!empty($trimmed)) {
            $genres[] = $trimmed;
        }
    }
}

// Wenn keine Genres da sind, zufällige Filme zeigen
$similarMovies = [];
try {
    if (!empty($genres)) {
        // Query bauen: Filme mit mindestens einem gleichen Genre
        $whereParts = [];
        $params = [':current_id' => $film['id']];
        
        foreach ($genres as $i => $genre) {
            $paramName = ":g{$i}";
            $whereParts[] = "genre LIKE {$paramName}";
            $params[$paramName] = '%' . $genre . '%';
        }
        
        $whereClause = implode(' OR ', $whereParts);
        
        $stmt = $pdo->prepare("
            SELECT id, title, year, genre, cover_id, rating_age 
            FROM dvds 
            WHERE id != :current_id 
            AND ($whereClause)
            ORDER BY RAND() 
            LIMIT 8
        ");
        $stmt->execute($params);
        $similarMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fallback: Wenn keine ähnlichen gefunden wurden (oder keine Genres da waren), einfach zufällige
    if (count($similarMovies) < 4) {
        $excludeIds = [$film['id']];
        foreach ($similarMovies as $sm) $excludeIds[] = $sm['id'];
        
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $stmt = $pdo->prepare("
            SELECT id, title, year, genre, cover_id, rating_age 
            FROM dvds 
            WHERE id NOT IN ($placeholders)
            ORDER BY RAND() 
            LIMIT " . (8 - count($similarMovies))
        );
        $stmt->execute($excludeIds);
        $randomMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $similarMovies = array_merge($similarMovies, $randomMovies);
    }
} catch (PDOException $e) {
    error_log("Similar movies local query error: " . $e->getMessage());
    return;
}

if (empty($similarMovies)) {
    return;
}
?>

<section class="similar-movies-section">
    <div class="section-header-compact">
        <h3>
            <i class="bi bi-collection-play"></i>
            Das könnte dir auch gefallen
        </h3>
        <span class="text-muted small">Aus deiner Sammlung</span>
    </div>
    
    <div class="similar-movies-grid">
        <?php foreach ($similarMovies as $movie): 
            $coverPath = findCoverImage($movie['cover_id'] ?? '', 'f');
        ?>
            <div class="similar-movie-card toggle-detail" data-id="<?= $movie['id'] ?>">
                <div class="card-image-wrapper">
                    <?php if ($coverPath && !str_contains($coverPath, 'placeholder.png')): ?>
                        <img src="<?= htmlspecialchars($coverPath) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="no-poster">
                            <i class="bi bi-film"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($movie['rating_age'])): 
                        $fskAge = (int)$movie['rating_age'];
                        $fskSvgPath = SVG_PATH . "/fsk/fsk-{$fskAge}.svg";
                    ?>
                        <div class="card-fsk-container">
                            <?php if (file_exists(BASE_PATH . '/' . $fskSvgPath)): ?>
                                <img src="<?= htmlspecialchars($fskSvgPath) ?>" 
                                     alt="FSK <?= $fskAge ?>" 
                                     class="card-fsk-img"
                                     title="FSK <?= $fskAge ?>">
                            <?php else: ?>
                                <div class="card-fsk-badge age-<?= $fskAge ?>">
                                    <?= $fskAge ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="similar-movie-info">
                    <h4><?= htmlspecialchars($movie['title']) ?></h4>
                    <div class="similar-movie-meta">
                        <?php if ($movie['year']): ?>
                            <span class="year"><?= $movie['year'] ?></span>
                        <?php endif; ?>
                        <?php if (!empty($movie['genre'])): ?>
                            <span class="genre"><?= htmlspecialchars(explode(',', $movie['genre'])[0]) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php
/**
 * Partials/stats.php - Vollständige Version mit PHP-Funktionen
 * Eigenständige Statistik-Seite mit Datenbankabfragen
 */

// Datenbankverbindung sicherstellen
global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Fallback: Bootstrap laden falls nicht vorhanden
    if (file_exists(__DIR__ . '/../includes/bootstrap.php')) {
        require_once __DIR__ . '/../includes/bootstrap.php';
    }
}

// Statistik-Funktionen
function getBasicStats($pdo): array {
    try {
        $stats = [];
        
        // Basis-Statistiken
        $stats['totalFilms'] = (int) $pdo->query("SELECT COUNT(*) FROM dvds")->fetchColumn();
        $stats['totalRuntime'] = (int) ($pdo->query("SELECT SUM(runtime) FROM dvds WHERE runtime > 0")->fetchColumn() ?: 0);
        $stats['avgRuntime'] = $stats['totalFilms'] > 0 ? round($stats['totalRuntime'] / $stats['totalFilms']) : 0;
        $stats['hours'] = round($stats['totalRuntime'] / 60);
        $stats['days'] = round($stats['hours'] / 24);
        
        // Jahr-Statistiken
        $yearStats = $pdo->query("
            SELECT 
                ROUND(AVG(year)) as avg_year,
                MIN(year) as oldest_year,
                MAX(year) as newest_year
            FROM dvds WHERE year > 0
        ")->fetch();
        
        $stats['avgYear'] = $yearStats['avg_year'] ?? date('Y');
        $stats['yearStats'] = $yearStats ?: ['oldest_year' => 'N/A', 'newest_year' => 'N/A'];
        
        return $stats;
    } catch (Exception $e) {
        error_log('Basic stats error: ' . $e->getMessage());
        return [
            'totalFilms' => 0, 'totalRuntime' => 0, 'avgRuntime' => 0,
            'hours' => 0, 'days' => 0, 'avgYear' => date('Y'),
            'yearStats' => ['oldest_year' => 'N/A', 'newest_year' => 'N/A']
        ];
    }
}

function getCollectionStats($pdo): array {
    try {
        return $pdo->query("
            SELECT 
                collection_type, 
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM dvds), 1) as percentage
            FROM dvds 
            WHERE collection_type IS NOT NULL 
            GROUP BY collection_type 
            ORDER BY count DESC
        ")->fetchAll();
    } catch (Exception $e) {
        error_log('Collection stats error: ' . $e->getMessage());
        return [
            ['collection_type' => 'DVD', 'count' => 0, 'percentage' => 0],
            ['collection_type' => 'Blu-ray', 'count' => 0, 'percentage' => 0]
        ];
    }
}

function getRatingStats($pdo): array {
    try {
        return $pdo->query("
            SELECT 
                rating_age, 
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM dvds), 1) as percentage
            FROM dvds 
            WHERE rating_age IS NOT NULL 
            GROUP BY rating_age 
            ORDER BY rating_age ASC
        ")->fetchAll();
    } catch (Exception $e) {
        error_log('Rating stats error: ' . $e->getMessage());
        return [
            ['rating_age' => 0, 'count' => 0, 'percentage' => 0],
            ['rating_age' => 12, 'count' => 0, 'percentage' => 0],
            ['rating_age' => 16, 'count' => 0, 'percentage' => 0]
        ];
    }
}

function getGenreStats($pdo): array {
    try {
        return $pdo->query("
            SELECT 
                TRIM(genre) as genre, 
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM dvds WHERE genre IS NOT NULL), 1) as percentage
            FROM dvds 
            WHERE genre IS NOT NULL AND genre != '' AND genre != 'NULL'
            GROUP BY TRIM(genre) 
            ORDER BY count DESC 
            LIMIT 8
        ")->fetchAll();
    } catch (Exception $e) {
        error_log('Genre stats error: ' . $e->getMessage());
        return [
            ['genre' => 'Action', 'count' => 0, 'percentage' => 0],
            ['genre' => 'Drama', 'count' => 0, 'percentage' => 0],
            ['genre' => 'Comedy', 'count' => 0, 'percentage' => 0]
        ];
    }
}

function getYearDistribution($pdo): array {
    try {
        return $pdo->query("
            SELECT 
                year, 
                COUNT(*) as count 
            FROM dvds 
            WHERE year > 0 AND year >= 1970 AND year <= " . date('Y') . "
            GROUP BY year 
            ORDER BY year ASC
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        error_log('Year distribution error: ' . $e->getMessage());
        return array_combine(range(2020, date('Y')), array_fill(0, date('Y') - 2019, 0));
    }
}

function getDecadeStats($pdo): array {
    try {
        return $pdo->query("
            SELECT 
                CONCAT(FLOOR(year/10)*10, 's') as decade,
                COUNT(*) as count,
                ROUND(AVG(runtime)) as avg_runtime
            FROM dvds 
            WHERE year > 0 
            GROUP BY FLOOR(year/10)*10 
            ORDER BY decade ASC
        ")->fetchAll();
    } catch (Exception $e) {
        error_log('Decade stats error: ' . $e->getMessage());
        return [
            ['decade' => '2020s', 'count' => 0, 'avg_runtime' => 120],
            ['decade' => '2010s', 'count' => 0, 'avg_runtime' => 115]
        ];
    }
}

function getNewestFilms($pdo): array {
    try {
        return $pdo->query("
            SELECT title, year, genre, created_at 
            FROM dvds 
            ORDER BY created_at DESC 
            LIMIT 8
        ")->fetchAll();
    } catch (Exception $e) {
        error_log('Newest films error: ' . $e->getMessage());
        return [];
    }
}

// Alle Statistiken laden
try {
    $basicStats = getBasicStats($pdo);
    $collections = getCollectionStats($pdo);
    $ratings = getRatingStats($pdo);
    $topGenres = getGenreStats($pdo);
    $yearDistribution = getYearDistribution($pdo);
    $decades = getDecadeStats($pdo);
    $boxsetData = getBoxsetStats($pdo);
    $newestFilms = getNewestFilms($pdo);
    
    // Variablen extrahieren für Template
    extract($basicStats);
    $boxsetStats = $boxsetData['stats'];
    $topBoxsets = $boxsetData['top'];
    
} catch (Exception $e) {
    error_log('Stats loading error: ' . $e->getMessage());
    $error_message = 'Fehler beim Laden der Statistiken: ' . $e->getMessage();
    
    // Fallback-Werte
    $totalFilms = $hours = $days = $avgRuntime = $avgYear = 0;
    $collections = $ratings = $topGenres = $decades = $newestFilms = $topBoxsets = [];
    $yearDistribution = [];
    $yearStats = $boxsetStats = ['total_boxsets' => 0, 'total_boxset_items' => 0];
}
?>

<div class="stats-page">
    <header class="page-header">
        <h1>
            <i class="bi bi-bar-chart-line"></i>
            Sammlungs-Statistiken
        </h1>
        <p class="page-subtitle">
            Detaillierte Analyse Ihrer <?= number_format($totalFilms) ?> Filme umfassenden Sammlung
        </p>
        <div class="stats-summary">
            Stand: <?= date('d.m.Y H:i') ?> Uhr | Version <?= defined('DVDPROFILER_VERSION') ? DVDPROFILER_VERSION : '1.0.0' ?>
        </div>
    </header>

    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php else: ?>

    <!-- Haupt-Statistik-Karten -->
    <section class="stats-overview">
        <div class="stat-cards-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="bi bi-collection-play"></i>
                </div>
                <div class="stat-content">
                    <h3><?= number_format($totalFilms) ?></h3>
                    <p>Filme insgesamt</p>
                    <small>in Ihrer Sammlung</small>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $days ?> Tage</h3>
                    <p>Gesamtlaufzeit</p>
                    <small><?= number_format($hours) ?> Stunden (⌀ <?= $avgRuntime ?> Min/Film)</small>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $avgYear ?></h3>
                    <p>Durchschnittsjahr</p>
                    <small>Spanne: <?= $yearStats['oldest_year'] ?> - <?= $yearStats['newest_year'] ?></small>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-content">
                    <h3><?= number_format($boxsetStats['total_boxsets']) ?></h3>
                    <p>BoxSet-Sammlungen</p>
                    <small><?= number_format($boxsetStats['total_boxset_items']) ?> Einzelfilme</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Diagramm-Bereich -->
    <section class="charts-grid">
        <!-- Collection Types -->
        <div class="chart-container">
            <div class="chart-header">
                <h3><i class="bi bi-pie-chart"></i> Sammlungstypen</h3>
                <p>Verteilung nach Medientyp</p>
            </div>
            <canvas id="collectionChart"></canvas>
            <div class="chart-legend">
                <?php foreach (array_slice($collections, 0, 4) as $collection): ?>
                    <span class="legend-item">
                        <strong><?= htmlspecialchars($collection['collection_type']) ?>:</strong> 
                        <?= $collection['count'] ?> (<?= $collection['percentage'] ?>%)
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Altersfreigaben -->
        <div class="chart-container">
            <div class="chart-header">
                <h3><i class="bi bi-shield-check"></i> Altersfreigaben</h3>
                <p>FSK-Verteilung der Sammlung</p>
            </div>
            <canvas id="ratingChart"></canvas>
        </div>

        <!-- Top Genres -->
        <div class="chart-container wide">
            <div class="chart-header">
                <h3><i class="bi bi-tags"></i> Beliebteste Genres</h3>
                <p>Top 8 Genres in Ihrer Sammlung</p>
            </div>
            <canvas id="genreChart"></canvas>
        </div>

        <!-- Timeline -->
        <div class="chart-container wide">
            <div class="chart-header">
                <h3><i class="bi bi-graph-up"></i> Filme pro Jahr</h3>
                <p>Chronologische Verteilung der Erscheinungsjahre</p>
            </div>
            <canvas id="yearChart"></canvas>
        </div>
    </section>

    <!-- Zusätzliche Informationen -->
    <section class="additional-stats">
        <div class="stats-grid">
            <!-- Dekaden-Übersicht -->
            <div class="info-card">
                <h3><i class="bi bi-calendar-range"></i> Filme nach Dekaden</h3>
                <div class="decade-list">
                    <?php foreach ($decades as $decade): ?>
                        <div class="decade-item">
                            <span class="decade-name"><?= htmlspecialchars($decade['decade']) ?></span>
                            <span class="decade-count"><?= $decade['count'] ?> Filme</span>
                            <span class="decade-runtime">⌀ <?= $decade['avg_runtime'] ?> Min</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top BoxSets -->
            <div class="info-card">
                <h3><i class="bi bi-collection"></i> Größte BoxSets</h3>
                <div class="boxset-list">
                    <?php foreach ($topBoxsets as $boxset): ?>
                        <div class="boxset-item">
                            <span class="boxset-name"><?= htmlspecialchars($boxset['boxset_name']) ?></span>
                            <span class="boxset-count"><?= $boxset['child_count'] ?> Filme</span>
                            <span class="boxset-runtime"><?= round(($boxset['total_runtime'] ?: 0) / 60) ?>h</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Zuletzt hinzugefügt -->
            <div class="info-card">
                <h3><i class="bi bi-plus-circle"></i> Zuletzt hinzugefügt</h3>
                <div class="recent-list">
                    <?php foreach ($newestFilms as $film): ?>
                        <div class="recent-item">
                            <span class="film-title"><?= htmlspecialchars($film['title']) ?></span>
                            <span class="film-year">(<?= $film['year'] ?>)</span>
                            <span class="film-date"><?= date('d.m.Y', strtotime($film['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php endif; ?>
</div>

<!-- Chart.js Datenbereitstellung -->
<script>
    // Daten für js/stats.js bereitstellen
    window.STATS_DATA = {
        collections: <?= json_encode($collections) ?>,
        ratings: <?= json_encode($ratings) ?>,
        genres: <?= json_encode($topGenres) ?>,
        years: <?= json_encode($yearDistribution) ?>
    };
</script>

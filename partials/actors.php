<?php
/**
 * Actors Overview Page - Schauspielerübersicht
 * Einfache Seite wie trailers.php - KEIN Fragment!
 * 
 * @package    dvdprofiler.liste
 * @version    1.5.0
 */

// $pdo ist bereits verfügbar von bootstrap.php

// Actor-Functions laden
if (!function_exists('getActorById')) {
    require_once __DIR__ . '/../includes/actor-functions.php';
}

// Filter-Parameter
$letter = isset($_GET['letter']) ? strtoupper(substr($_GET['letter'], 0, 1)) : '';

// Alle Schauspieler laden
$sql = "SELECT id, first_name, last_name, slug, birth_year, photo FROM actor WHERE 1=1";
$params = [];

if (!empty($letter) && preg_match('/^[A-Z]$/', $letter)) {
    $sql .= " AND (last_name LIKE :letter OR first_name LIKE :letter)";
    $params[':letter'] = $letter . '%';
}

$sql .= " ORDER BY last_name ASC, first_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gruppierung nach Buchstaben
$actorsByLetter = [];
$availableLetters = [];

foreach ($actors as $actor) {
    $sortName = !empty($actor['last_name']) ? $actor['last_name'] : $actor['first_name'];
    $firstLetter = strtoupper(mb_substr($sortName, 0, 1));
    
    if (!preg_match('/^[A-Z]$/', $firstLetter)) {
        $firstLetter = '#';
    }
    
    $actorsByLetter[$firstLetter][] = [
        'name' => trim($actor['first_name'] . ' ' . $actor['last_name']),
        'slug' => $actor['slug'],
        'birth_year' => $actor['birth_year'],
        'photo' => $actor['photo']
    ];
    
    $availableLetters[$firstLetter] = true;
}

ksort($actorsByLetter);
if (isset($actorsByLetter['#'])) {
    $temp = $actorsByLetter['#'];
    unset($actorsByLetter['#']);
    $actorsByLetter['#'] = $temp;
}

$totalActors = count($actors);
?>

<div class="actors-overview-container">
    <div class="overview-header">
        <h1 class="overview-title">
            <i class="bi bi-people-fill"></i> Schauspieler
        </h1>
        <div class="overview-stats">
            <span class="stat-badge">
                <i class="bi bi-person-badge"></i> <?= $totalActors ?> Schauspieler
            </span>
            <?php if (!empty($letter)): ?>
            <span class="stat-badge filter-active">
                <i class="bi bi-funnel-fill"></i> Buchstabe: <?= htmlspecialchars($letter) ?>
                <a href="?page=actors" class="remove-filter">×</a>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="alphabet-nav">
        <a href="?page=actors" class="letter-link<?= empty($letter) ? ' active' : '' ?>">Alle</a>
        <?php
        $alphabet = range('A', 'Z');
        $alphabet[] = '#';
        foreach ($alphabet as $char):
            $hasActors = isset($availableLetters[$char]);
            $isActive = ($letter === $char);
        ?>
        <a href="?page=actors&letter=<?= $char ?>" 
           class="letter-link<?= $isActive ? ' active' : '' ?><?= !$hasActors ? ' disabled' : '' ?>">
            <?= $char ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($actorsByLetter)): ?>
        <div class="no-results">
            <i class="bi bi-search" style="font-size: 4rem; opacity: 0.2;"></i>
            <h3>Keine Schauspieler gefunden</h3>
            <p><?= !empty($letter) ? "Keine Schauspieler mit Buchstabe \"$letter\"" : "Keine Schauspieler in der Datenbank" ?></p>
            <?php if (!empty($letter)): ?>
            <a href="?page=actors" class="btn btn-primary">Alle anzeigen</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($actorsByLetter as $currentLetter => $actorsInGroup): ?>
        <div class="letter-group">
            <h2 class="letter-heading">
                <span class="letter-badge"><?= $currentLetter ?></span>
                <span class="letter-count"><?= count($actorsInGroup) ?> Schauspieler</span>
            </h2>
            
            <div class="actors-grid">
                <?php foreach ($actorsInGroup as $actor): ?>
                <div class="actor-card">
                    <a href="#" class="actor-card-link actor-link" data-actor-slug="<?= htmlspecialchars($actor['slug']) ?>">
                        <div class="actor-photo">
                            <?php
                            $photoPath = 'actors/' . $actor['photo'];
                            if (!empty($actor['photo']) && file_exists($photoPath)):
                            ?>
                                <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($actor['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="actor-photo-placeholder">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actor-info">
                            <h3 class="actor-name"><?= htmlspecialchars($actor['name']) ?></h3>
                            <?php if (!empty($actor['birth_year'])): ?>
                            <p class="actor-birth-year">
                                <i class="bi bi-calendar3"></i> <?= htmlspecialchars($actor['birth_year']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actor-overlay">
                            <i class="bi bi-arrow-right-circle"></i>
                            <span>Profil anzeigen</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.actors-overview-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.overview-header {
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.overview-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-color, #e0e0e0);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.overview-title i {
    color: var(--primary-color, #4EC9B0);
}

.overview-stats {
    display: flex;
    gap: 10px;
}

.stat-badge {
    background: rgba(78, 201, 176, 0.1);
    color: var(--primary-color, #4EC9B0);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-badge.filter-active {
    background: rgba(255, 215, 0, 0.1);
    color: #ffd700;
}

.remove-filter {
    color: inherit;
    font-size: 1.2rem;
    font-weight: bold;
    margin-left: 4px;
    text-decoration: none;
}

.alphabet-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 30px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
}

.letter-link {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-color, #e0e0e0);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
}

.letter-link:hover:not(.disabled) {
    background: var(--primary-color, #4EC9B0);
    color: #1e1e1e;
    transform: translateY(-2px);
}

.letter-link.active {
    background: var(--primary-color, #4EC9B0);
    color: #1e1e1e;
    font-weight: 600;
}

.letter-link.disabled {
    opacity: 0.3;
    pointer-events: none;
}

.letter-group {
    margin-bottom: 50px;
}

.letter-heading {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(78, 201, 176, 0.2);
}

.letter-badge {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color, #4EC9B0), #569cd6);
    color: #1e1e1e;
    font-size: 1.5rem;
    font-weight: bold;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(78, 201, 176, 0.3);
}

.letter-count {
    color: var(--text-muted, #999);
}

.actors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}

.actor-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
    border: 1px solid rgba(255, 255, 255, 0.05);
    position: relative;
}

.actor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    border-color: var(--primary-color, #4EC9B0);
}

.actor-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.actor-photo {
    width: 100%;
    aspect-ratio: 2/3;
    background: rgba(255, 255, 255, 0.05);
    overflow: hidden;
}

.actor-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.actor-card:hover .actor-photo img {
    transform: scale(1.05);
}

.actor-photo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(78, 201, 176, 0.05), rgba(86, 156, 214, 0.05));
}

.actor-info {
    padding: 15px;
}

.actor-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 6px 0;
    color: var(--text-color, #e0e0e0);
}

.actor-birth-year {
    font-size: 0.85rem;
    color: var(--text-muted, #999);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

.actor-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(78, 201, 176, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.3s;
    color: #1e1e1e;
    font-weight: 600;
}

.actor-card:hover .actor-overlay {
    opacity: 1;
}

.actor-overlay i {
    font-size: 2rem;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
}

@media (max-width: 768px) {
    .actors-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
    .overview-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
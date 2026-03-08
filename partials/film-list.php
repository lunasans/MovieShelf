<?php
// partials/film-list.php - BoxSet mit Overlay-Modal

// Bootstrap laden für Datenbankverbindung
if (!isset($pdo)) {
    require_once __DIR__ . '/../includes/bootstrap.php';
}

// Einfache, sichere Datenabfrage
$search = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');
$page = max(1, (int)($_GET['seite'] ?? 1));

// Filme pro Seite aus Settings laden (Standard: 20)
$perPage = (int)getSetting('items_per_page', '20');

// Validierung (5-100)
if ($perPage < 5) $perPage = 5;
if ($perPage > 100) $perPage = 100;

$offset = ($page - 1) * $perPage;

try {
    // Collection Types laden
    $typesStmt = $pdo->query("SELECT DISTINCT collection_type FROM dvds WHERE collection_type IS NOT NULL AND deleted = 0 ORDER BY collection_type");
    $types = $typesStmt ? $typesStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    
    // WHERE-Filter aufbauen
    $where = ['1=1', 'deleted = 0']; // Gelöschte Filme ausschließen
    $params = [];
    
    if ($search !== '') {
        $where[] = "title LIKE :search";
        $params['search'] = "%{$search}%";
    }
    if ($type !== '') {
        $where[] = "collection_type = :type";
        $params['type'] = $type;
    }
    
    $whereSql = 'WHERE ' . implode(' AND ', $where);
    
    // WICHTIG: Filtere Children raus! (boxset_parent IS NULL = Parents + Einzelfilme)
    $whereSql .= ' AND boxset_parent IS NULL';
    
    // Gesamtanzahl (NUR Parents und Einzelfilme - KEINE Children!)
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM dvds $whereSql");
    if ($countStmt) {
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);
    } else {
        $total = 0;
        $totalPages = 0;
    }
    
    // Filme laden MIT BoxSet-Info (NUR Parents und Einzelfilme - KEINE Children!)
    $sql = "SELECT d.*, 
                   (SELECT COUNT(*) FROM dvds WHERE boxset_parent = d.id AND deleted = 0) as children_count
            FROM dvds d 
            $whereSql
            ORDER BY title 
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    if ($stmt) {
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $films = $stmt->fetchAll();
    } else {
        $films = [];
    }
    
} catch (Exception $e) {
    error_log('Film-list error: ' . $e->getMessage());
    $types = [];
    $films = [];
    $total = 0;
    $totalPages = 0;
}

?>

<!-- Tabs für Collection Types -->
<div class="tabs-wrapper">
  <ul class="tabs">
    <li class="<?= $type === '' ? 'active' : '' ?>">
      <a href="?<?= buildQuery(['type' => '', 'seite' => 1]) ?>">Alle</a>
    </li>
    <?php foreach ($types as $t): ?>
      <li class="<?= $type === $t ? 'active' : '' ?>">
        <a href="?<?= buildQuery(['type' => $t, 'seite' => 1]) ?>"><?= htmlspecialchars($t) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
  
  <!-- View Mode Toggle -->
  <div class="view-toggle">
    <button class="view-btn" data-mode="grid" title="Kachel-Ansicht">
      <i class="bi bi-grid-3x3-gap"></i>
    </button>
    <button class="view-btn" data-mode="list" title="Listen-Ansicht">
      <i class="bi bi-list-ul"></i>
    </button>
  </div>
</div>

<!-- Film-Liste -->
<div class="film-list">
  <?php if (empty($films)): ?>
    <div class="empty-state">
      <i class="bi bi-film"></i>
      <h3>Keine Filme gefunden</h3>
      <p>
        <?php if (!empty($search)): ?>
          Keine Filme gefunden für "<?= htmlspecialchars($search) ?>".
        <?php elseif (!empty($type)): ?>
          Keine Filme im Genre "<?= htmlspecialchars($type) ?>" gefunden.
        <?php else: ?>
          Noch keine Filme in der Sammlung vorhanden.
        <?php endif; ?>
      </p>
    </div>
  <?php else: ?>
    <?php foreach ($films as $film): ?>
      <?= renderFilmCard($film) ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
  <nav class="pagination">
    <?php if ($page > 1): ?>
      <a href="?<?= buildQuery(['seite' => 1]) ?>">« Erste</a>
      <a href="?<?= buildQuery(['seite' => $page - 1]) ?>">‹ Zurück</a>
    <?php endif; ?>
    
    <?php
    $window = 2;
    $start = max(1, $page - $window);
    $end = min($totalPages, $page + $window);
    
    if ($start > 1): ?>
      <a href="?<?= buildQuery(['seite' => 1]) ?>">1</a>
      <?php if ($start > 2): ?>
        <span class="dots">...</span>
      <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
      <?php if ($i === $page): ?>
        <span class="current"><?= $i ?></span>
      <?php else: ?>
        <a href="?<?= buildQuery(['seite' => $i]) ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $totalPages): ?>
      <?php if ($end < $totalPages - 1): ?>
        <span class="dots">...</span>
      <?php endif; ?>
      <a href="?<?= buildQuery(['seite' => $totalPages]) ?>"><?= $totalPages ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
      <a href="?<?= buildQuery(['seite' => $page + 1]) ?>">Weiter ›</a>
      <a href="?<?= buildQuery(['seite' => $totalPages]) ?>">Letzte »</a>
    <?php endif; ?>
  </nav>
  
  <div class="pagination-info">
    Seite <?= $page ?> von <?= $totalPages ?> (<?= $total ?> Filme insgesamt)
  </div>
<?php endif; ?>

<!-- BoxSet Overlay Modal -->
<div id="boxsetModal" class="boxset-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">
                <i class="bi bi-collection-play"></i>
                <span>BoxSet</span>
            </h2>
            <button class="modal-close" onclick="closeBoxSetModal()" aria-label="Schließen">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">
                <i class="bi bi-hourglass-split"></i>
                Lade Filme...
            </div>
        </div>
    </div>
</div>
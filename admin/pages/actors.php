<?php
/**
 * DVD Profiler Liste - Actors Management
 * Verwalte, bearbeite und lösche Schauspieler
 * 
 * @package    dvdprofiler.liste
 * @version    1.4.8
 */

// Sicherheitscheck
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Actor Functions laden (falls noch nicht geladen)
if (!function_exists('getAllActors')) {
    require_once __DIR__ . '/../../includes/actor-functions.php';
}

// CSRF-Token generieren
$csrfToken = generateCSRFToken();

// Success/Error Messages
$success = '';
$error = '';

if (isset($_SESSION['actors_success'])) {
    $success = $_SESSION['actors_success'];
    unset($_SESSION['actors_success']);
}

if (isset($_SESSION['actors_error'])) {
    $error = $_SESSION['actors_error'];
    unset($_SESSION['actors_error']);
}

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Search
$search = $_GET['search'] ?? '';

// Sorting
$sortColumn = $_GET['sort'] ?? 'id';
$sortOrder = $_GET['order'] ?? 'desc';

// Validate sort column
$allowedColumns = ['id', 'first_name', 'last_name', 'birth_date', 'nationality', 'created_at'];
if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'id';
}

// Validate sort order
$sortOrder = strtolower($sortOrder);
if (!in_array($sortOrder, ['asc', 'desc'])) {
    $sortOrder = 'desc';
}

// Helper function for sortable column headers
function getActorSortUrl($column, $currentSort, $currentOrder, $search) {
    // Toggle order if same column, otherwise default to ASC
    if ($currentSort === $column) {
        $newOrder = ($currentOrder === 'asc') ? 'desc' : 'asc';
    } else {
        $newOrder = 'asc';
    }
    
    // Build URL with all parameters
    $params = [
        'page' => 'actors',
        'sort' => $column,
        'order' => $newOrder
    ];
    
    if (!empty($search)) {
        $params['search'] = $search;
    }
    
    return '?' . http_build_query($params);
}

// Helper function for pagination URLs
function getActorPaginationUrl($page, $search, $sortColumn, $sortOrder) {
    $params = [
        'page' => 'actors',
        'p' => $page
    ];
    
    if (!empty($search)) {
        $params['search'] = $search;
    }
    
    if (!empty($sortColumn) && $sortColumn !== 'id') {
        $params['sort'] = $sortColumn;
    }
    
    if (!empty($sortOrder) && $sortOrder !== 'desc') {
        $params['order'] = $sortOrder;
    }
    
    return '?' . http_build_query($params);
}

// Helper function to get sort icon
function getActorSortIcon($column, $currentSort, $currentOrder) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 0.8em;"></i>';
    }
    
    if ($currentOrder === 'asc') {
        return '<i class="bi bi-arrow-up ms-1" style="font-size: 0.8em;"></i>';
    } else {
        return '<i class="bi bi-arrow-down ms-1" style="font-size: 0.8em;"></i>';
    }
}

// Build Query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR nationality LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM actors 
    " . $whereClause
);
$stmt->execute($params);
$totalActors = $stmt->fetchColumn();
$totalPages = ceil($totalActors / $perPage);

// Build ORDER BY with sortable columns
$orderBy = match($sortColumn) {
    'first_name' => "first_name $sortOrder, last_name $sortOrder",
    'last_name' => "last_name $sortOrder, first_name $sortOrder",
    'birth_date' => "birth_date $sortOrder",
    'nationality' => "nationality $sortOrder",
    'created_at' => "created_at $sortOrder",
    default => "id $sortOrder"
};

// Load actors with film count
$sql = "
    SELECT 
        a.id, a.first_name, a.last_name, a.slug, 
        a.birth_date, a.birth_place, a.nationality, 
        a.photo_path, a.view_count, a.created_at,
        COUNT(DISTINCT fa.film_id) as film_count
    FROM actors a
    LEFT JOIN film_actor fa ON a.id = fa.actor_id
    " . $whereClause . "
    GROUP BY a.id
    ORDER BY " . $orderBy . "
    LIMIT ? OFFSET ?
";

// Add limit and offset to params
$allParams = array_merge($params, [$perPage, $offset]);

$stmt = $pdo->prepare($sql);
$stmt->execute($allParams);
$actors = $stmt->fetchAll();

// Löschen-Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_actor'])) {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['actors_error'] = 'Ungültiger Sicherheitstoken';
    } else {
        $actorId = (int)$_POST['actor_id'];
        if (deleteActor($pdo, $actorId)) {
            $_SESSION['actors_success'] = 'Schauspieler erfolgreich gelöscht';
        } else {
            $_SESSION['actors_error'] = 'Fehler beim Löschen des Schauspielers';
        }
    }
    
    // Reload page
    header('Location: ?page=actors&p=' . $page . ($search ? '&search=' . urlencode($search) : ''));
    exit;
}
?>

<style>
/* ============================================
   ACTORS PAGE - ULTRA-DUNKLES THEME
   Mit MAXIMALER Priorität - IDENTISCH mit films.php!
   ============================================ */

/* TABELLE & CONTAINER - SEHR DUNKEL! */
.table-responsive {
    background: var(--clr-card) !important;
    border-radius: var(--radius);
}

.table {
    background: var(--clr-card) !important;
    color: var(--clr-text) !important;
    margin-bottom: 0 !important;
}

.table thead {
    background: rgba(255, 255, 255, 0.1) !important;
}

.table thead th {
    background: rgba(255, 255, 255, 0.1) !important;
    color: var(--clr-text) !important;
    border-bottom: 1px solid var(--clr-border) !important;
    font-weight: 600;
    padding: 1rem !important;
}

.table tbody {
    background: var(--clr-card) !important;
}

.table tbody tr {
    background: var(--clr-card) !important;
}

.table tbody td {
    background: transparent !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: var(--clr-text) !important;
    padding: 1rem !important;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: rgba(255, 255, 255, 0.05) !important;
}

.table tbody tr:hover td {
    background: transparent !important;
}

/* Name in Tabelle - WEIß */
.table tbody td strong {
    color: var(--clr-text) !important;
    font-weight: 600;
}

/* CARDS - DUNKEL */
.card {
    background: var(--clr-card) !important;
    border: 1px solid var(--clr-border) !important;
    color: var(--clr-text) !important;
}

.card-header {
    background: rgba(255, 255, 255, 0.05) !important;
    border-bottom: 1px solid var(--clr-border) !important;
    color: var(--clr-text) !important;
}

.card-body {
    background: var(--clr-card) !important;
    color: var(--clr-text) !important;
}

/* FORMS - DUNKEL */
.form-control, .form-select {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid var(--clr-border) !important;
    color: var(--clr-text) !important;
}

.form-control:focus, .form-select:focus {
    background: rgba(255, 255, 255, 0.15) !important;
    border-color: var(--clr-accent) !important;
    color: var(--clr-text) !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

.form-label {
    color: var(--clr-text) !important;
}

/* ALERTS - DUNKEL */
.alert {
    border: 1px solid !important;
}

.alert-success {
    background: rgba(40, 167, 69, 0.15) !important;
    border-color: rgba(40, 167, 69, 0.3) !important;
    color: #75dd8c !important;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.15) !important;
    border-color: rgba(220, 53, 69, 0.3) !important;
    color: #ff6b7a !important;
}

.alert-info {
    background: rgba(13, 202, 240, 0.15) !important;
    border-color: rgba(13, 202, 240, 0.3) !important;
    color: #4dd4ff !important;
}

/* BREADCRUMB */
.breadcrumb {
    background: transparent !important;
    padding: 0.75rem 0 !important;
}

.breadcrumb-item a {
    color: var(--clr-accent) !important;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: var(--clr-text) !important;
}

.breadcrumb-item.active {
    color: var(--clr-text) !important;
}

/* TEXT UTILITIES */
.text-muted {
    color: var(--clr-text-muted) !important;
}

small.text-muted {
    color: var(--clr-text-muted) !important;
}

/* BUTTONS */
.btn-group .btn {
    border: 1px solid;
}

.btn-outline-primary {
    border-color: var(--clr-accent);
    color: var(--clr-accent);
}

.btn-outline-info {
    border-color: var(--clr-info);
    color: var(--clr-info);
}

.btn-outline-danger {
    border-color: var(--clr-danger);
    color: var(--clr-danger);
}

/* Pagination */
.pagination .page-link {
    background: var(--clr-card) !important;
    border-color: var(--clr-border) !important;
    color: var(--clr-text) !important;
}

.pagination .page-item.active .page-link {
    background: var(--clr-accent) !important;
    border-color: var(--clr-accent) !important;
    color: #ffffff !important;
}

/* Sortable Table Headers */
.table thead th a {
    color: var(--clr-text) !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    transition: var(--transition);
}

.table thead th a:hover {
    color: var(--clr-accent) !important;
}

.table thead th a i {
    margin-left: 0.25rem;
    font-size: 0.8em;
}

.table thead th a i.text-muted {
    opacity: 0.5;
}

.table thead th a:hover i.text-muted {
    opacity: 1;
    color: var(--clr-accent) !important;
}

/* Badge Styling */
.badge {
    font-weight: 600;
    padding: 0.35em 0.65em;
}

/* Actor Photo Styling */
.actor-photo {
    width: 50px;
    height: 75px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--clr-border);
}

.actor-photo-placeholder {
    width: 50px;
    height: 75px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    border: 1px solid var(--clr-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--clr-text-muted);
}
</style>

<div class="container-fluid px-4">
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Schauspieler</li>
    </ol>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="page" value="actors">
                
                <div class="col-md-6">
                    <label for="search" class="form-label">Suche</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Name oder Nationalität...">
                </div>
                
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Suchen
                    </button>
                    <a href="?page=actors" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Zurücksetzen
                    </a>
                    <a href="?page=actor-edit" class="btn btn-success ms-auto">
                        <i class="bi bi-plus-circle"></i> Neuer Schauspieler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Actors Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-people-fill"></i> <?= number_format($totalActors) ?> Schauspieler
                <?php if (!empty($search)): ?>
                    <span class="badge bg-primary">Gefiltert</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($actors)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Keine Schauspieler gefunden.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;">
                                    <a href="<?= getActorSortUrl('id', $sortColumn, $sortOrder, $search) ?>">
                                        ID<?= getActorSortIcon('id', $sortColumn, $sortOrder) ?>
                                    </a>
                                </th>
                                <th style="width: 100px;">Foto</th>
                                <th>
                                    <a href="<?= getActorSortUrl('first_name', $sortColumn, $sortOrder, $search) ?>">
                                        Name<?= getActorSortIcon('first_name', $sortColumn, $sortOrder) ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?= getActorSortUrl('birth_date', $sortColumn, $sortOrder, $search) ?>">
                                        Geboren<?= getActorSortIcon('birth_date', $sortColumn, $sortOrder) ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?= getActorSortUrl('nationality', $sortColumn, $sortOrder, $search) ?>">
                                        Nationalität<?= getActorSortIcon('nationality', $sortColumn, $sortOrder) ?>
                                    </a>
                                </th>
                                <th style="width: 100px;" class="text-center">Filme</th>
                                <th style="width: 100px;" class="text-center">Aufrufe</th>
                                <th style="width: 200px;" class="text-end">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actors as $actor): ?>
                            <?php
                            $actorName = htmlspecialchars(trim($actor['first_name'] . ' ' . $actor['last_name']));
                            $actorSlug = htmlspecialchars($actor['slug'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= $actor['id'] ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($actor['photo_path'])): ?>
                                    <img 
                                        src="../<?= htmlspecialchars($actor['photo_path']) ?>" 
                                        alt="<?= $actorName ?>"
                                        class="actor-photo"
                                    >
                                    <?php else: ?>
                                    <div class="actor-photo-placeholder">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $actorName ?></strong>
                                    <br>
                                    <small class="text-muted"><?= $actorSlug ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($actor['birth_date'])): ?>
                                    <?= date('d.m.Y', strtotime($actor['birth_date'])) ?>
                                    <?php if (!empty($actor['birth_place'])): ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($actor['birth_place']) ?></small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($actor['nationality']) ? htmlspecialchars($actor['nationality']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($actor['film_count'] ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= number_format($actor['view_count'] ?? 0) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="../?page=actor&slug=<?= urlencode($actorSlug) ?>" 
                                           class="btn btn-outline-info" 
                                           title="Profil ansehen"
                                           target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="?page=actor-edit&id=<?= $actor['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Bearbeiten">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Löschen"
                                            onclick="confirmDelete(<?= $actor['id'] ?>, '<?= addslashes($actorName) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Actors Pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= getActorPaginationUrl($page - 1, $search, $sortColumn, $sortOrder) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Pages -->
                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);
                        
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . getActorPaginationUrl(1, $search, $sortColumn, $sortOrder) . '">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $start; $i <= $end; $i++) {
                            $active = $i === $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . getActorPaginationUrl($i, $search, $sortColumn, $sortOrder) . '">' . $i . '</a></li>';
                        }
                        
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . getActorPaginationUrl($totalPages, $search, $sortColumn, $sortOrder) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Next -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= getActorPaginationUrl($page + 1, $search, $sortColumn, $sortOrder) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="delete_actor" value="1">
    <input type="hidden" name="actor_id" id="deleteActorId">
</form>

<script>
function confirmDelete(actorId, actorName) {
    if (confirm('Wirklich löschen?\n\nSchauspieler: ' + actorName + '\n\nAlle Verknüpfungen zu Filmen werden ebenfalls entfernt.')) {
        document.getElementById('deleteActorId').value = actorId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
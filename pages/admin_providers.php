<?php
require_admin();
$societyId = admin_society_id(); // null for super admin

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif ((string) ($_POST['action'] ?? '') === 'add') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        if ($name === '') {
            $error = 'Provider name is required.';
        } elseif ($societyId && !$categoryId) {
            $error = 'Choose a category for this provider.';
        } else {
            $extId = 'manual-' . bin2hex(random_bytes(6));
            db()->prepare('INSERT INTO providers (ext_id, name, address, specialization, website, priority) VALUES (?,?,"","","",5)')->execute([$extId, $name]);
            $newId = (int) db()->lastInsertId();
            if ($societyId) {
                // Sub admin: immediately list the new provider under their own society.
                db()->prepare('INSERT IGNORE INTO society_providers (society_id, category_id, provider_id) VALUES (?,?,?)')
                    ->execute([$societyId, $categoryId, $newId]);
            }
            redirect(admin_url('providers/' . $newId) . '?added=1');
        }
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 40;
$offset = ($page - 1) * $perPage;

if ($societyId) {
    // Sub admin only ever sees providers listed under their own society.
    if ($q !== '') {
        $like = '%' . $q . '%';
        $total = db()->prepare('SELECT COUNT(DISTINCT p.id) FROM providers p JOIN society_providers sp ON sp.provider_id = p.id WHERE sp.society_id = ? AND (p.name LIKE ? OR p.specialization LIKE ?)');
        $total->execute([$societyId, $like, $like]);
        $total = (int) $total->fetchColumn();
        $st = db()->prepare('SELECT DISTINCT p.* FROM providers p JOIN society_providers sp ON sp.provider_id = p.id WHERE sp.society_id = ? AND (p.name LIKE ? OR p.specialization LIKE ?) ORDER BY p.name LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $st->execute([$societyId, $like, $like]);
    } else {
        $total = db()->prepare('SELECT COUNT(DISTINCT p.id) FROM providers p JOIN society_providers sp ON sp.provider_id = p.id WHERE sp.society_id = ?');
        $total->execute([$societyId]);
        $total = (int) $total->fetchColumn();
        $st = db()->prepare('SELECT DISTINCT p.* FROM providers p JOIN society_providers sp ON sp.provider_id = p.id WHERE sp.society_id = ? ORDER BY p.name LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $st->execute([$societyId]);
    }
} elseif ($q !== '') {
    $like = '%' . $q . '%';
    $total = db()->prepare('SELECT COUNT(*) FROM providers WHERE name LIKE ? OR specialization LIKE ?');
    $total->execute([$like, $like]);
    $total = (int) $total->fetchColumn();
    $st = db()->prepare('SELECT * FROM providers WHERE name LIKE ? OR specialization LIKE ? ORDER BY name LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $st->execute([$like, $like]);
} else {
    $total = (int) db()->query('SELECT COUNT(*) FROM providers')->fetchColumn();
    $st = db()->query('SELECT * FROM providers ORDER BY name LIMIT ' . $perPage . ' OFFSET ' . $offset);
}
$providers = $st->fetchAll();

$ids = array_column($providers, 'id');
$ratings = $ids ? ratings_for($ids) : [];

$categories = $societyId ? db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll() : [];

if (isset($_GET['added'])) $success = 'Provider added. Fill in details below.';

render_admin('admin_providers_view', [
    'title' => 'Providers | Admin | ' . cfg('site', 'name'),
    'providers' => $providers, 'ratings' => $ratings, 'error' => $error, 'success' => $success,
    'q' => $q, 'page' => $page, 'perPage' => $perPage, 'total' => $total,
    'categories' => $categories,
]);

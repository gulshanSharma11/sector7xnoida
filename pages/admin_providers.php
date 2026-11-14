<?php
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif ((string) ($_POST['action'] ?? '') === 'add') {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $error = 'Provider name is required.';
        } else {
            $extId = 'manual-' . bin2hex(random_bytes(6));
            db()->prepare('INSERT INTO providers (ext_id, name, address, specialization, website, priority) VALUES (?,?,"","","",5)')->execute([$extId, $name]);
            $newId = (int) db()->lastInsertId();
            redirect(admin_url('providers/' . $newId) . '?added=1');
        }
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 40;
$offset = ($page - 1) * $perPage;

if ($q !== '') {
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

if (isset($_GET['added'])) $success = 'Provider added. Fill in details below.';

render_admin('admin_providers_view', [
    'title' => 'Providers | Admin | ' . cfg('site', 'name'),
    'providers' => $providers, 'ratings' => $ratings, 'error' => $error, 'success' => $success,
    'q' => $q, 'page' => $page, 'perPage' => $perPage, 'total' => $total,
]);

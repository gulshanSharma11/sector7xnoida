<?php
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                $error = 'Society name is required.';
            } else {
                $slug = slugify($name);
                $base = $slug; $i = 2;
                while (true) {
                    $st = db()->prepare('SELECT COUNT(*) FROM societies WHERE slug = ?');
                    $st->execute([$slug]);
                    if ((int) $st->fetchColumn() === 0) break;
                    $slug = $base . '-' . $i++;
                }
                db()->prepare('INSERT INTO societies (name, slug, legacy_slug, mongo_id) VALUES (?,?,"","")')->execute([$name, $slug]);
                redirect(admin_url('societies') . '?added=1');
            }
        } elseif ($action === 'rename') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($id && $name !== '') {
                db()->prepare('UPDATE societies SET name = ? WHERE id = ?')->execute([$name, $id]);
                redirect(admin_url('societies') . '?saved=1');
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id) {
                db()->prepare('DELETE FROM societies WHERE id = ?')->execute([$id]);
                redirect(admin_url('societies') . '?deleted=1');
            }
        }
    }
}

if (isset($_GET['added'])) $success = 'Society added.';
if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['deleted'])) $success = 'Society deleted.';

$q = trim((string) ($_GET['q'] ?? ''));
if ($q !== '') {
    $st = db()->prepare(
        'SELECT s.*, (SELECT COUNT(*) FROM society_providers sp WHERE sp.society_id = s.id) AS provider_count
         FROM societies s WHERE s.name LIKE ? ORDER BY s.name LIMIT 300'
    );
    $st->execute(['%' . $q . '%']);
} else {
    $st = db()->query(
        'SELECT s.*, (SELECT COUNT(*) FROM society_providers sp WHERE sp.society_id = s.id) AS provider_count
         FROM societies s ORDER BY s.name LIMIT 300'
    );
}
$societies = $st->fetchAll();

render_admin('admin_societies_view', [
    'title' => 'Societies | Admin | ' . cfg('site', 'name'),
    'societies' => $societies, 'error' => $error, 'success' => $success, 'q' => $q,
]);

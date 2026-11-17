<?php
require_super_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $icon = trim((string) ($_POST['icon'] ?? ''));
            if ($name === '') {
                $error = 'Category name is required.';
            } else {
                $slug = slugify($name);
                $base = $slug; $i = 2;
                while (true) {
                    $st = db()->prepare('SELECT COUNT(*) FROM categories WHERE slug = ?');
                    $st->execute([$slug]);
                    if ((int) $st->fetchColumn() === 0) break;
                    $slug = $base . '-' . $i++;
                }
                db()->prepare('INSERT INTO categories (name, slug, icon) VALUES (?,?,?)')->execute([$name, $slug, $icon]);
                redirect(admin_url('categories') . '?added=1');
            }
        } elseif ($action === 'save') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $icon = trim((string) ($_POST['icon'] ?? ''));
            if ($id && $name !== '') {
                db()->prepare('UPDATE categories SET name = ?, icon = ? WHERE id = ?')->execute([$name, $icon, $id]);
                redirect(admin_url('categories') . '?saved=1');
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id) {
                db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
                redirect(admin_url('categories') . '?deleted=1');
            }
        }
    }
}

if (isset($_GET['added'])) $success = 'Category added.';
if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['deleted'])) $success = 'Category deleted.';

$categories = db()->query(
    'SELECT c.*, (SELECT COUNT(*) FROM society_providers sp WHERE sp.category_id = c.id) AS link_count
     FROM categories c ORDER BY c.name'
)->fetchAll();

render_admin('admin_categories_view', [
    'title' => 'Categories | Admin | ' . cfg('site', 'name'),
    'categories' => $categories, 'error' => $error, 'success' => $success,
]);

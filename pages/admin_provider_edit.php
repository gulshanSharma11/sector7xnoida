<?php
require_admin();
$societyId = admin_society_id(); // null for super admin

$id = (int) ($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM providers WHERE id = ?');
$st->execute([$id]);
$provider = $st->fetch();
if (!$provider) not_found();

if ($societyId) {
    // Sub admin: only allowed to touch a provider that is listed under their own society.
    $check = db()->prepare('SELECT COUNT(*) FROM society_providers WHERE provider_id = ? AND society_id = ?');
    $check->execute([$id, $societyId]);
    if ((int) $check->fetchColumn() === 0) not_found();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        // Sub admins can add & update, never delete.
        $subBlocked = $societyId && in_array($action, ['delete_phone', 'delete_link', 'delete_provider'], true);

        if ($subBlocked) {
            $error = 'Only a super admin can delete this.';
        } elseif ($action === 'save_details') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $address = trim((string) ($_POST['address'] ?? ''));
            $spec = trim((string) ($_POST['specialization'] ?? ''));
            $website = trim((string) ($_POST['website'] ?? ''));
            $isNew = !empty($_POST['is_new']);
            if ($name === '') {
                $error = 'Name is required.';
            } else {
                db()->prepare('UPDATE providers SET name=?, address=?, specialization=?, website=?, priority=? WHERE id=?')
                    ->execute([$name, $address, $spec, $website, $isNew ? 1 : 5, $id]);
                redirect(admin_url('providers/' . $id) . '?saved=1');
            }
        } elseif ($action === 'add_phone') {
            $raw = trim((string) ($_POST['phone'] ?? ''));
            $displayOverride = trim((string) ($_POST['display'] ?? ''));
            if ($raw === '') {
                $error = 'Enter a phone number.';
            } else {
                $norm = normalize_phone($raw, $displayOverride);
                $nextSort = db()->prepare('SELECT COALESCE(MAX(sort),0)+1 FROM provider_phones WHERE provider_id = ?');
                $nextSort->execute([$id]);
                db()->prepare('INSERT INTO provider_phones (provider_id, display, tel, whatsapp, is_mobile, sort) VALUES (?,?,?,?,?,?)')
                    ->execute([$id, $norm['display'], $norm['tel'], $norm['whatsapp'], $norm['is_mobile'], (int) $nextSort->fetchColumn()]);
                redirect(admin_url('providers/' . $id) . '?saved=1');
            }
        } elseif ($action === 'delete_phone') {
            $phoneId = (int) ($_POST['phone_id'] ?? 0);
            db()->prepare('DELETE FROM provider_phones WHERE id = ? AND provider_id = ?')->execute([$phoneId, $id]);
            redirect(admin_url('providers/' . $id) . '?saved=1');
        } elseif ($action === 'add_link') {
            // Sub admin can only ever link into their own society.
            $linkSocietyId = $societyId ?: (int) ($_POST['society_id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            if ($linkSocietyId && $categoryId) {
                db()->prepare('INSERT IGNORE INTO society_providers (society_id, category_id, provider_id) VALUES (?,?,?)')
                    ->execute([$linkSocietyId, $categoryId, $id]);
                redirect(admin_url('providers/' . $id) . '?saved=1');
            }
            $error = 'Choose a category.';
        } elseif ($action === 'delete_link') {
            $linkSocietyId = (int) ($_POST['society_id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            db()->prepare('DELETE FROM society_providers WHERE society_id = ? AND category_id = ? AND provider_id = ?')
                ->execute([$linkSocietyId, $categoryId, $id]);
            redirect(admin_url('providers/' . $id) . '?saved=1');
        } elseif ($action === 'delete_provider') {
            db()->prepare('DELETE FROM providers WHERE id = ?')->execute([$id]);
            redirect(admin_url('providers') . '?deleted=1');
        }
    }
}

if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['added'])) $success = 'Provider added. Fill in details below.';

$phones = db()->prepare('SELECT * FROM provider_phones WHERE provider_id = ? ORDER BY sort');
$phones->execute([$id]);
$phones = $phones->fetchAll();

if ($societyId) {
    // Sub admin only sees/manages this provider's listing(s) within their own society.
    $links = db()->prepare(
        'SELECT sp.society_id, sp.category_id, s.name AS society_name, c.name AS category_name
         FROM society_providers sp JOIN societies s ON s.id = sp.society_id JOIN categories c ON c.id = sp.category_id
         WHERE sp.provider_id = ? AND sp.society_id = ? ORDER BY c.name'
    );
    $links->execute([$id, $societyId]);
} else {
    $links = db()->prepare(
        'SELECT sp.society_id, sp.category_id, s.name AS society_name, c.name AS category_name
         FROM society_providers sp JOIN societies s ON s.id = sp.society_id JOIN categories c ON c.id = sp.category_id
         WHERE sp.provider_id = ? ORDER BY s.name, c.name'
    );
    $links->execute([$id]);
}
$links = $links->fetchAll();

$allSocieties = $societyId ? [] : db()->query('SELECT id, name FROM societies ORDER BY name')->fetchAll();
$allCategories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$ratingStats = ratings_for([$id]);
$rating = $ratingStats[$id] ?? null;

$reviews = db()->prepare(
    'SELECT r.id, r.rating, r.comment, r.pinned, r.updated_at, u.mobile, u.country_code
     FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.provider_id = ? ORDER BY r.pinned DESC, r.updated_at DESC'
);
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

render_admin('admin_provider_edit_view', [
    'title' => $provider['name'] . ' | Admin | ' . cfg('site', 'name'),
    'provider' => $provider, 'phones' => $phones, 'links' => $links,
    'allSocieties' => $allSocieties, 'allCategories' => $allCategories,
    'rating' => $rating, 'reviews' => $reviews,
    'error' => $error, 'success' => $success,
]);

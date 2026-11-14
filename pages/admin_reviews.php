<?php
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $reviewId = (int) ($_POST['id'] ?? 0);
        if ($action === 'pin') {
            db()->prepare('UPDATE reviews SET pinned = 1 WHERE id = ?')->execute([$reviewId]);
            redirect(admin_url('reviews') . '?saved=1');
        } elseif ($action === 'unpin') {
            db()->prepare('UPDATE reviews SET pinned = 0 WHERE id = ?')->execute([$reviewId]);
            redirect(admin_url('reviews') . '?saved=1');
        } elseif ($action === 'edit') {
            $rating = (int) ($_POST['rating'] ?? 0);
            $comment = trim((string) ($_POST['comment'] ?? ''));
            $comment = mb_substr(preg_replace('/\s+/u', ' ', $comment) ?? '', 0, 600);
            if ($rating >= 1 && $rating <= 5) {
                db()->prepare('UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?')->execute([$rating, $comment, $reviewId]);
                redirect(admin_url('reviews') . '?saved=1');
            }
            $error = 'Rating must be 1-5.';
        } elseif ($action === 'delete') {
            db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([$reviewId]);
            redirect(admin_url('reviews') . '?deleted=1');
        }
    }
}

if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['deleted'])) $success = 'Review deleted.';

$filter = trim((string) ($_GET['q'] ?? ''));
$minRating = (int) ($_GET['min'] ?? 0);

$where = [];
$args = [];
if ($filter !== '') { $where[] = '(p.name LIKE ? OR r.comment LIKE ? OR u.mobile LIKE ?)'; $args[] = "%$filter%"; $args[] = "%$filter%"; $args[] = "%$filter%"; }
if ($minRating >= 1 && $minRating <= 5) { $where[] = 'r.rating >= ?'; $args[] = $minRating; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$st = db()->prepare(
    "SELECT r.id, r.rating, r.comment, r.pinned, r.updated_at, p.id AS provider_id, p.name AS provider_name, u.mobile, u.country_code
     FROM reviews r JOIN providers p ON p.id = r.provider_id JOIN users u ON u.id = r.user_id
     $whereSql
     ORDER BY r.pinned DESC, r.updated_at DESC
     LIMIT 300"
);
$st->execute($args);
$reviews = $st->fetchAll();

render_admin('admin_reviews_view', [
    'title' => 'Reviews & ratings | Admin | ' . cfg('site', 'name'),
    'reviews' => $reviews, 'error' => $error, 'success' => $success, 'q' => $filter, 'min' => $minRating,
]);

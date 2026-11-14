<?php
require_admin();

$counts = [
    'societies'  => (int) db()->query('SELECT COUNT(*) FROM societies')->fetchColumn(),
    'categories' => (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'providers'  => (int) db()->query('SELECT COUNT(*) FROM providers')->fetchColumn(),
    'phones'     => (int) db()->query('SELECT COUNT(*) FROM provider_phones')->fetchColumn(),
    'reviews'    => (int) db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    'users'      => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$recentReviews = db()->query(
    "SELECT r.id, r.rating, r.comment, r.pinned, r.updated_at, p.id AS provider_id, p.name AS provider_name, u.mobile, u.country_code
     FROM reviews r JOIN providers p ON p.id = r.provider_id JOIN users u ON u.id = r.user_id
     ORDER BY r.updated_at DESC LIMIT 8"
)->fetchAll();

render_admin('admin_dashboard_view', [
    'title' => 'Dashboard | Admin | ' . cfg('site', 'name'),
    'counts' => $counts, 'recentReviews' => $recentReviews,
]);

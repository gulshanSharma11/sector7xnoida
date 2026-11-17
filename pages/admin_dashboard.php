<?php
require_admin();

if (is_super_admin()) {
    $society = null;
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
} else {
    $societyId = admin_society_id();
    $st = db()->prepare('SELECT * FROM societies WHERE id = ?');
    $st->execute([$societyId]);
    $society = $st->fetch();
    if (!$society) not_found();

    $pst = db()->prepare('SELECT COUNT(DISTINCT provider_id) FROM society_providers WHERE society_id = ?');
    $pst->execute([$societyId]);
    $counts = [
        'providers' => (int) $pst->fetchColumn(),
    ];
    $cst = db()->prepare('SELECT COUNT(DISTINCT category_id) FROM society_providers WHERE society_id = ?');
    $cst->execute([$societyId]);
    $counts['categories'] = (int) $cst->fetchColumn();

    $rst = db()->prepare(
        "SELECT COUNT(*) FROM reviews r WHERE r.provider_id IN (SELECT provider_id FROM society_providers WHERE society_id = ?)"
    );
    $rst->execute([$societyId]);
    $counts['reviews'] = (int) $rst->fetchColumn();

    $recentReviews = db()->prepare(
        "SELECT r.id, r.rating, r.comment, r.pinned, r.updated_at, p.id AS provider_id, p.name AS provider_name, u.mobile, u.country_code
         FROM reviews r JOIN providers p ON p.id = r.provider_id JOIN users u ON u.id = r.user_id
         WHERE r.provider_id IN (SELECT provider_id FROM society_providers WHERE society_id = ?)
         ORDER BY r.updated_at DESC LIMIT 8"
    );
    $recentReviews->execute([$societyId]);
    $recentReviews = $recentReviews->fetchAll();
}

render_admin('admin_dashboard_view', [
    'title' => 'Dashboard | Admin | ' . cfg('site', 'name'),
    'counts' => $counts, 'recentReviews' => $recentReviews, 'society' => $society,
]);

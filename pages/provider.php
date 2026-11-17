<?php
/**
 * Single provider page: contact details, average rating, and reviews.
 * Only a logged-in user (mobile OTP login) can add/edit/delete a review, one review per user per provider.
 */
$id = (int) ($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM providers WHERE id = ? LIMIT 1');
$st->execute([$id]);
$provider = $st->fetch();
if (!$provider) not_found();

$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user) {
        redirect(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? ('/provider/' . $id))));
    }
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');
        if ($action === 'delete') {
            db()->prepare('DELETE FROM reviews WHERE provider_id = ? AND user_id = ?')->execute([$id, $user['id']]);
            redirect(url('/provider/' . $id . '-' . slugify($provider['name'])) . '?removed=1');
        }

        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim((string) ($_POST['comment'] ?? ''));
        $comment = mb_substr(preg_replace('/\s+/u', ' ', $comment), 0, 600);

        if ($rating < 1 || $rating > 5) {
            $error = 'Please choose a star rating from 1 to 5.';
        } else {
            $st = db()->prepare(
                'INSERT INTO reviews (provider_id, user_id, rating, comment, created_at, updated_at)
                 VALUES (?,?,?,?,NOW(),NOW())
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()'
            );
            $st->execute([$id, $user['id'], $rating, $comment]);
            redirect(url('/provider/' . $id . '-' . slugify($provider['name'])) . '?saved=1');
        }
    }
}

// This provider's own review from the logged-in user, if any (prefills the form)
$myReview = null;
if ($user) {
    $st = db()->prepare('SELECT rating, comment FROM reviews WHERE provider_id = ? AND user_id = ?');
    $st->execute([$id, $user['id']]);
    $myReview = $st->fetch() ?: null;
}

// Where this provider is listed (society + category)
$st = db()->prepare(
    'SELECT s.name AS society_name, s.slug AS society_slug, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
     FROM society_providers sp
     JOIN societies s ON s.id = sp.society_id
     JOIN categories c ON c.id = sp.category_id
     WHERE sp.provider_id = ?
     ORDER BY s.name'
);
$st->execute([$id]);
$listings = $st->fetchAll();

$phones = db()->prepare('SELECT * FROM provider_phones WHERE provider_id = ? ORDER BY sort');
$phones->execute([$id]);
$phones = $phones->fetchAll();

$ratingStats = ratings_for([$id]);
$rating = $ratingStats[$id] ?? null;

$st = db()->prepare(
    'SELECT r.rating, r.comment, r.updated_at, r.user_id, r.pinned, u.mobile, u.country_code
     FROM reviews r JOIN users u ON u.id = r.user_id
     WHERE r.provider_id = ?
     ORDER BY r.pinned DESC, r.updated_at DESC
     LIMIT 100'
);
$st->execute([$id]);
$reviews = $st->fetchAll();

if (isset($_GET['saved'])) $success = 'Thanks! Your review has been saved.';
if (isset($_GET['removed'])) $success = 'Your review has been removed.';

$title = $provider['name'] . ' - Reviews & Contact | ' . cfg('site', 'name');
$desc  = $provider['specialization'] !== ''
    ? $provider['name'] . ' (' . $provider['specialization'] . ') - ratings, reviews and contact number.'
    : $provider['name'] . ' - ratings, reviews and contact number for Noida societies.';

render('provider_view', [
    'title' => $title, 'desc' => $desc,
    'provider' => $provider, 'listings' => $listings, 'phones' => $phones,
    'rating' => $rating, 'reviews' => $reviews, 'myReview' => $myReview,
    'error' => $error, 'success' => $success,
    'canonical' => abs_url('/provider/' . $id . '-' . slugify($provider['name'])),
    'jsonld' => $rating && $rating['count'] > 0 ? [[
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $provider['name'],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating['avg'],
            'reviewCount' => $rating['count'],
        ],
    ]] : [],
]);

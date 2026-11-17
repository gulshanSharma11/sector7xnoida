<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = base_path();
if ($base !== '' && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base));
$path = '/' . trim(rawurldecode($uri), '/');
if ($path === '/index.php') $path = '/';

// Serve real static files (CSS/JS/images/ad uploads) directly from PHP.
// Some hosts don't apply .htaccess's "serve real files directly" rewrite rule
// reliably, so every request - even for /assets/style.css - ends up here. This
// bypasses that entirely: no .htaccess cooperation needed.
if (preg_match('~^/(assets|uploads)/~', $path)) {
    $rel = ltrim($path, '/');
    $full = realpath(S7X_ROOT . '/' . $rel);
    $allowedBase = realpath(S7X_ROOT);
    if ($full && $allowedBase && str_starts_with($full, $allowedBase . DIRECTORY_SEPARATOR) && is_file($full)) {
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css', 'js' => 'application/javascript',
            'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp',
            'ico' => 'image/x-icon', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
            'mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime',
            'txt' => 'text/plain',
        ];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($full));
        header('Cache-Control: public, max-age=2592000');
        readfile($full);
        exit;
    }
    not_found();
}

try {
    switch (true) {
        case $path === '/':
            require __DIR__ . '/pages/home.php';
            break;
        case (bool) preg_match('~^/society/([A-Za-z0-9._-]+)$~', $path, $m):
            $_GET['slug'] = $m[1];
            require __DIR__ . '/pages/society.php';
            break;
        case (bool) preg_match('~^/provider/(\d+)(?:-[A-Za-z0-9-]*)?$~', $path, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/pages/provider.php';
            break;
        case $path === '/search':
            require __DIR__ . '/pages/search.php';
            break;
        case $path === '/login':
            require __DIR__ . '/pages/login.php';
            break;
        case $path === '/logout':
            require __DIR__ . '/pages/logout.php';
            break;
        case $path === '/switch-society':
            forget_society();
            redirect(url('/') . '#societies');
            break;
        case $path === '/admin/login':
            require __DIR__ . '/pages/admin_login.php';
            break;
        case $path === '/admin/logout':
            require __DIR__ . '/pages/admin_logout.php';
            break;
        case $path === '/admin':
            require __DIR__ . '/pages/admin_dashboard.php';
            break;
        case $path === '/admin/societies':
            require __DIR__ . '/pages/admin_societies.php';
            break;
        case $path === '/admin/categories':
            require __DIR__ . '/pages/admin_categories.php';
            break;
        case $path === '/admin/providers':
            require __DIR__ . '/pages/admin_providers.php';
            break;
        case (bool) preg_match('~^/admin/providers/(\d+)$~', $path, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/pages/admin_provider_edit.php';
            break;
        case $path === '/admin/reviews':
            require __DIR__ . '/pages/admin_reviews.php';
            break;
        case $path === '/admin/ads':
            require __DIR__ . '/pages/admin_ads.php';
            break;
        case $path === '/admin/admins':
            require __DIR__ . '/pages/admin_admins.php';
            break;
        case (bool) preg_match('~^/ad/(\d+)$~', $path, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/pages/ad_click.php';
            break;
        case $path === '/sitemap.xml':
            require __DIR__ . '/pages/sitemap.php';
            break;
        case $path === '/robots.txt':
            header('Content-Type: text/plain; charset=utf-8');
            echo "User-agent: *\nAllow: /\nDisallow: /login\nDisallow: /logout\nDisallow: /admin\n\nSitemap: " . abs_url('/sitemap.xml') . "\n";
            break;
        default:
            not_found();
    }
} catch (PDOException $ex) {
    error_log('[Sector7xNoida] DB error: ' . $ex->getMessage());
    http_response_code(500);
    if (cfg('debug', null, false) || !empty($GLOBALS['S7X_CFG']['debug'])) {
        echo '<pre>' . e($ex->getMessage()) . '</pre>';
    } else {
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Temporarily unavailable</title><body style="font-family:system-ui;padding:3rem;text-align:center"><h1>We will be right back</h1><p>The site could not reach its database. Please try again in a few minutes.</p></body>';
    }
}

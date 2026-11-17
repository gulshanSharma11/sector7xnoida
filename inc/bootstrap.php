<?php
declare(strict_types=1);

define('S7X_ROOT', dirname(__DIR__));
$GLOBALS['S7X_CFG'] = require __DIR__ . '/config.php';

if (!empty($GLOBALS['S7X_CFG']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Kolkata');

function cfg(string $section, ?string $key = null, $default = null)
{
    $c = $GLOBALS['S7X_CFG'][$section] ?? null;
    if ($key === null) return $c ?? $default;
    return $c[$key] ?? $default;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/* ---------- Session ---------- */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) (60 * 60 * 24 * 30));
    session_name('s7xsid');
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- Database ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = cfg('db');
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $c['host'], $c['name'], $c['charset']);
    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/* ---------- Helpers ---------- */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    $b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $b === '.' ? '' : $b;
}

function url(string $path = '/'): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function abs_url(string $path = '/'): string
{
    return rtrim((string) cfg('site', 'url'), '/') . '/' . ltrim($path, '/');
}

function asset(string $file): string
{
    $f = S7X_ROOT . '/assets/' . $file;
    $v = is_file($f) ? filemtime($f) : 1;
    return url('assets/' . $file) . '?v=' . $v;
}

function plural(int $n, string $one, string $many): string
{
    return $n . ' ' . ($n === 1 ? $one : $many);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    $t = $_POST['_csrf'] ?? '';
    return is_string($t) && $t !== '' && hash_equals($_SESSION['csrf'] ?? '', $t);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function can_see_contact(): bool
{
    return !cfg('auth', 'require_login_for_contact', false) || current_user() !== null;
}

function safe_next(?string $next): string
{
    $next = (string) $next;
    if ($next === '' || $next[0] !== '/' || str_starts_with($next, '//') || str_contains($next, '\\')) {
        return url('/');
    }
    return $next;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function mask_mobile(string $m): string
{
    return strlen($m) >= 6 ? substr($m, 0, 2) . str_repeat('x', strlen($m) - 4) . substr($m, -2) : $m;
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

/* ---------- "Remember my society" (cookie, 1 year) ----------
 * Set whenever a visitor opens a society page. Read on the home page so a
 * returning visitor sees a "Continue to <their society>" shortcut above the
 * full list - they can always pick a different society from the list below,
 * this never forces them into one. */
const S7X_SOCIETY_COOKIE = 's7x_society';

function remember_society(string $slug): void
{
    setcookie(S7X_SOCIETY_COOKIE, $slug, [
        'expires'  => time() + 60 * 60 * 24 * 365,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function forget_society(): void
{
    setcookie(S7X_SOCIETY_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** @return array{id:int,name:string,slug:string}|null */
function remembered_society(): ?array
{
    $slug = (string) ($_COOKIE[S7X_SOCIETY_COOKIE] ?? '');
    if ($slug === '') return null;
    $st = db()->prepare('SELECT id, name, slug FROM societies WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    $row = $st->fetch();
    return $row ?: null;
}

/* ---------- Rendering ---------- */
function render(string $view, array $data = []): void
{
    $data += ['title' => cfg('site', 'name'), 'desc' => cfg('site', 'tagline'), 'canonical' => null, 'robots' => 'index,follow', 'jsonld' => []];
    extract($data, EXTR_SKIP);
    ob_start();
    require S7X_ROOT . '/pages/' . $view . '.php';
    $content = ob_get_clean();
    require S7X_ROOT . '/inc/layout.php';
}

function not_found(): never
{
    http_response_code(404);
    render('404', ['title' => 'Page not found | ' . cfg('site', 'name'), 'robots' => 'noindex,follow']);
    exit;
}

/* ---------- Admin ---------- */
function admin_url(string $path = ''): string
{
    $path = trim($path, '/');
    return url('/admin' . ($path !== '' ? '/' . $path : ''));
}

function is_admin(): bool
{
    return !empty($_SESSION['admin']['id']);
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

/** Super admin = full power. Sub admin = scoped to one society (add/update only, no delete). */
function is_super_admin(): bool
{
    return (current_admin()['role'] ?? '') === 'super';
}

function admin_society_id(): ?int
{
    $s = current_admin()['society_id'] ?? null;
    return $s !== null ? (int) $s : null;
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect(admin_url('login') . '?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? admin_url()));
        exit;
    }
}

/** Use on pages only a super admin may open (society list, categories, ads, admin accounts). */
function require_super_admin(): void
{
    require_admin();
    if (!is_super_admin()) {
        http_response_code(403);
        render_admin('admin_forbidden_view', ['title' => 'Not allowed | Admin | ' . cfg('site', 'name')]);
        exit;
    }
}

/**
 * A sub admin may only touch the society they were assigned. Returns true for super admins
 * (no restriction) or sub admins whose society_id matches $societyId.
 */
function admin_can_manage_society(int $societyId): bool
{
    return is_super_admin() || admin_society_id() === $societyId;
}

function render_admin(string $view, array $data = []): void
{
    $data += ['title' => 'Admin | ' . cfg('site', 'name')];
    extract($data, EXTR_SKIP);
    ob_start();
    require S7X_ROOT . '/pages/' . $view . '.php';
    $content = ob_get_clean();
    require S7X_ROOT . '/inc/admin_layout.php';
}

/** Bare shell (no sidebar) for the admin login screen. */
function render_admin_auth(string $view, array $data = []): void
{
    $data += ['title' => 'Admin login | ' . cfg('site', 'name')];
    extract($data, EXTR_SKIP);
    ob_start();
    require S7X_ROOT . '/pages/' . $view . '.php';
    $content = ob_get_clean();
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?></title>
<link rel="icon" href="<?= e(asset('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('admin.css')) ?>">
</head>
<body class="admin-body">
<?= $content ?>
</body>
</html>
<?php
}

/**
 * Raw 10-digit (or landline) number -> {display, tel, whatsapp, is_mobile}.
 * Mobile = 10 digits starting 6-9 -> gets +91 tel link + WhatsApp link.
 * Anything else (landline with STD code etc.) -> tel link only, no WhatsApp.
 */
function normalize_phone(string $raw, string $displayOverride = ''): array
{
    $digits = preg_replace('/\D/', '', $raw) ?? '';
    if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
        $digits = substr($digits, 2);
    }
    $isMobile = (bool) preg_match('/^[6-9]\d{9}$/', $digits);
    if ($isMobile) {
        return [
            'display'   => $displayOverride !== '' ? $displayOverride : $digits,
            'tel'       => '+91' . $digits,
            'whatsapp'  => '91' . $digits,
            'is_mobile' => 1,
        ];
    }
    return [
        'display'   => $displayOverride !== '' ? $displayOverride : ($digits !== '' ? $digits : $raw),
        'tel'       => $digits !== '' ? $digits : preg_replace('/\s+/', '', $raw),
        'whatsapp'  => null,
        'is_mobile' => 0,
    ];
}

/* ---------- Ratings ---------- */
function slugify(string $s): string
{
    $s = preg_replace('~[^a-z0-9]+~', '-', mb_strtolower($s));
    return trim((string) $s, '-') ?: 'provider';
}

function provider_url(array $p): string
{
    return url('/provider/' . (int) $p['id'] . '-' . slugify($p['name']));
}

/** @return array<int,array{avg:float,count:int}> */
function ratings_for(array $providerIds): array
{
    $ids = array_values(array_unique(array_map('intval', $providerIds)));
    if (!$ids) return [];
    $in = implode(',', $ids);
    $rows = db()->query("SELECT provider_id, ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE provider_id IN ($in) GROUP BY provider_id")->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[(int) $r['provider_id']] = ['avg' => (float) $r['avg_rating'], 'count' => (int) $r['cnt']];
    return $out;
}

function stars_html(float $avg, string $size = ''): string
{
    $pct = max(0, min(100, ($avg / 5) * 100));
    return '<span class="stars' . ($size ? ' ' . e($size) : '') . '" style="--pct:' . round($pct, 1) . '%" aria-hidden="true">★★★★★</span>';
}

function rating_summary(?array $r): string
{
    if (!$r || $r['count'] === 0) {
        return '<span class="prating prating-empty muted">No ratings yet</span>';
    }
    return '<span class="prating">' . stars_html($r['avg']) . ' <b>' . e(number_format($r['avg'], 1)) . '</b> <span class="muted">(' . plural($r['count'], 'review', 'reviews') . ')</span></span>';
}

/* ---------- Provider card ---------- */
function provider_card(array $p, array $phones, ?array $rating = null): string
{
    $show = can_see_contact();
    $isNew = (int) $p['priority'] === 1;
    $search = mb_strtolower($p['name'] . ' ' . $p['specialization'] . ' ' . $p['address']);
    $h  = '<article class="pcard" data-search="' . e($search) . '">';
    $h .= '<div class="pcard-top"><h4>' . e($p['name']) . '</h4>';
    if ($isNew) $h .= '<span class="badge badge-new">New</span>';
    $h .= '</div>';
    $h .= '<div class="pcard-rating"><a href="' . e(provider_url($p)) . '">' . rating_summary($rating) . '</a></div>';
    if ($p['specialization'] !== '') $h .= '<p class="pspec">' . e($p['specialization']) . '</p>';
    if ($p['address'] !== '') {
        $h .= '<p class="paddr"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>' . e($p['address']) . '</p>';
    }
    $h .= '<div class="pphones">';
    if (!$phones) {
        $h .= '<span class="nophone">No number listed</span>';
    } elseif (!$show) {
        $h .= '<a class="btn btn-ghost btn-sm" href="' . e(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'))) . '">Login to view number</a>';
    } else {
        foreach ($phones as $ph) {
            $h .= '<div class="prow"><a class="btn btn-call" href="tel:' . e($ph['tel']) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>' . e($ph['display']) . '</a>';
            if (!empty($ph['whatsapp'])) {
                $h .= '<a class="btn btn-wa" target="_blank" rel="noopener" href="https://wa.me/' . e($ph['whatsapp']) . '" aria-label="WhatsApp ' . e($p['name']) . '">WhatsApp</a>';
            }
            $h .= '</div>';
        }
    }
    $h .= '</div>';
    if ($p['website'] !== '') {
        $w = $p['website'];
        $href = preg_match('~^https?://~i', $w) ? $w : 'https://' . $w;
        $h .= '<a class="pweb" target="_blank" rel="noopener nofollow" href="' . e($href) . '">Visit website &rarr;</a>';
    }
    $h .= '<a class="btn btn-ghost btn-sm prate-link" href="' . e(provider_url($p)) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.6 5.6 6.1.6-4.6 4.1 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4.1 6.1-.6z"/></svg>Rate &amp; review</a>';
    return $h . '</article>';
}

function group_phones(array $rows): array
{
    $out = [];
    foreach ($rows as $r) $out[(int) $r['provider_id']][] = $r;
    return $out;
}

/* ---------- Advertisements ---------- */

/** Where uploaded ad images live. Deliberately OUTSIDE assets/ so a future git deploy
 *  (which only copies the assets/ folder from the repo) never wipes an uploaded image. */
function ads_upload_dir(): string
{
    return S7X_ROOT . '/uploads/ads';
}

function ads_upload_url(string $file): string
{
    return url('/uploads/ads/' . $file);
}

/** @return array<int,array<string,mixed>> */
function active_ads(string $placement, int $limit = 3): array
{
    static $cache = [];
    if (isset($cache[$placement])) return $cache[$placement];
    $st = db()->prepare('SELECT * FROM ads WHERE placement = ? AND active = 1 ORDER BY sort ASC, id DESC LIMIT ' . max(1, $limit));
    $st->execute([$placement]);
    return $cache[$placement] = $st->fetchAll();
}

function ad_is_video(string $file): bool
{
    return (bool) preg_match('~\.(mp4|webm|mov)(\?.*)?$~i', $file);
}

function ad_card_html(array $ad): string
{
    $src = preg_match('~^https?://~i', $ad['image_url']) ? $ad['image_url'] : ads_upload_url($ad['image_url']);
    $href = $ad['link_url'] !== '' ? url('/ad/' . (int) $ad['id']) : '#';
    // If the image/video URL fails to load (e.g. a hotlink-protected search-engine proxy link),
    // hide the whole card instead of showing a broken-image icon.
    $onerror = "this.closest('.ad-card').style.display='none'";
    $media = ad_is_video($ad['image_url'])
        ? '<video src="' . e($src) . '" autoplay muted loop playsinline onerror="' . e($onerror) . '"></video>'
        : '<img src="' . e($src) . '" alt="' . e($ad['title']) . '" loading="lazy" onerror="' . e($onerror) . '">';
    return '<a class="ad-card" href="' . e($href) . '" target="_blank" rel="noopener sponsored nofollow" title="' . e($ad['title']) . '">'
        . $media . '<span class="ad-tag">Advertisement</span></a>';
}

/** Sidebar rail - used on pages with a two-column listing layout (society, provider).
 *  Shows up to 10 ads; if more than ~3 fit, the rail itself scrolls (see .ad-rail CSS)
 *  so the page layout never breaks no matter how many ads get added. */
function render_ad_rail(): string
{
    $ads = active_ads('rail', 10);
    if (!$ads) return '';
    $h = '<aside class="ad-rail" aria-label="Advertisement">';
    foreach ($ads as $ad) $h .= ad_card_html($ad);
    $h .= '</aside>';
    return $h;
}

/** Full-width horizontal strip - used between sections (home page etc).
 *  One ad = a plain centered banner. Multiple ads = a horizontally scrollable strip. */
function render_ad_banner(): string
{
    $ads = active_ads('banner', 10);
    if (!$ads) return '';
    $multi = count($ads) > 1;
    $h = '<div class="wrap"><div class="ad-banner' . ($multi ? ' ad-banner-multi' : '') . '">';
    foreach ($ads as $ad) $h .= ad_card_html($ad);
    $h .= '</div></div>';
    return $h;
}

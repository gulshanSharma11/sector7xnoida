<?php
/** @var string $content @var string $title @var string $desc */
$user = current_user();
$site = cfg('site', 'name');
$canon = $canonical ?? abs_url(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
$here = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="robots" content="<?= e($robots) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta name="theme-color" content="#fff9ee">
<link rel="icon" href="<?= e(asset('favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('style.css')) ?>">
<?php foreach ($jsonld as $j): ?>
<script type="application/ld+json"><?= json_encode($j, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
  <div class="wrap bar">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e($site) ?> home">
      <svg class="mark" viewBox="0 0 32 32" aria-hidden="true"><rect width="32" height="32" rx="9" fill="currentColor"/><path d="M9 21.5V12l7-4.5 7 4.5v9.5" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/><path d="M13.5 21.5v-5h5v5" fill="none" stroke="#f6a43c" stroke-width="2" stroke-linejoin="round"/></svg>
      <span>Sector<b>7x</b>Noida</span>
    </a>
    <nav class="nav" aria-label="Main">
      <a href="<?= e(url('/#societies')) ?>">Societies</a>
      <a href="<?= e(url('/search')) ?>" <?= $here === url('/search') ? 'aria-current="page"' : '' ?>>Search</a>
      <?php if ($user): ?>
        <span class="who" title="Logged in"><?= e('+' . $user['country_code'] . ' ' . mask_mobile($user['mobile'])) ?></span>
        <form method="post" action="<?= e(url('/logout')) ?>" class="inline"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit">Logout</button></form>
      <?php else: ?>
        <a class="btn btn-primary btn-sm" href="<?= e(url('/login')) ?>">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <div class="brand brand-foot"><span>Sector<b>7x</b>Noida</span></div>
        <p>Neighbourhood directory of doctors, pandits, RO technicians, plumbers, milkmen and other everyday services, organised society by society.</p>
      </div>
      <div>
        <h5>Explore</h5>
        <a href="<?= e(url('/#societies')) ?>">All societies</a>
        <a href="<?= e(url('/search')) ?>">Search contacts</a>
        <a href="<?= e(url('/login')) ?>">Login</a>
      </div>
      <div>
        <h5>Something wrong?</h5>
        <?php $wa = preg_replace('/\D/', '', (string) cfg('site', 'contact_whatsapp', '')); ?>
        <?php if ($wa): ?>
          <p>Wrong number or want to add a service? <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Hi, I want to update the Sector7xNoida directory.') ?>" target="_blank" rel="noopener">Message us on WhatsApp</a>.</p>
        <?php else: ?>
          <p>Found a wrong number or want to add a service? Please let us know.</p>
        <?php endif; ?>
      </div>
    </div>
    <p class="disclaimer">Contact details are shared for convenience and may change. Please verify before hiring. <?= e($site) ?> is not affiliated with the listed service providers.</p>
    <p class="copy">&copy; <?= date('Y') ?> <?= e($site) ?></p>
  </div>
</footer>
<script src="<?= e(asset('app.js')) ?>" defer></script>
</body>
</html>

<?php
/** @var string $content @var string $title */
$here = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$navItems = [
    ['dashboard',  admin_url(''),           'Dashboard'],
    ['societies',  admin_url('societies'),  'Societies'],
    ['categories', admin_url('categories'), 'Categories'],
    ['providers',  admin_url('providers'),  'Providers'],
    ['reviews',    admin_url('reviews'),    'Reviews &amp; ratings'],
];
function nav_active(string $href, string $here): bool
{
    if ($href === admin_url('') && $here === admin_url('')) return true;
    return $href !== admin_url('') && str_starts_with($here, $href);
}
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
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <a class="admin-brand" href="<?= e(admin_url('')) ?>">
      <svg class="mark" viewBox="0 0 32 32" aria-hidden="true"><rect width="32" height="32" rx="9" fill="currentColor"/><path d="M9 21.5V12l7-4.5 7 4.5v9.5" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/><path d="M13.5 21.5v-5h5v5" fill="none" stroke="#f6a43c" stroke-width="2" stroke-linejoin="round"/></svg>
      <span>Sector<b>7x</b> Admin</span>
    </a>
    <nav class="admin-nav" aria-label="Admin">
      <?php foreach ($navItems as [$key, $href, $label]): ?>
        <a href="<?= e($href) ?>" class="<?= nav_active($href, $here) ? 'active' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">View live site &rarr;</a>
      <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm btn-block" type="submit">Logout</button></form>
    </div>
  </aside>
  <main class="admin-main">
    <?= $content ?>
  </main>
</div>
</body>
</html>

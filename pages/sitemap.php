<?php
header('Content-Type: application/xml; charset=utf-8');
$today = date('Y-m-d');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
echo '<url><loc>' . e(abs_url('/')) . '</loc><lastmod>' . $today . '</lastmod><priority>1.0</priority></url>' . "\n";
echo '<url><loc>' . e(abs_url('/search')) . '</loc><priority>0.5</priority></url>' . "\n";
foreach (db()->query('SELECT slug FROM societies ORDER BY name') as $s) {
    echo '<url><loc>' . e(abs_url('/society/' . $s['slug'])) . '</loc><lastmod>' . $today . '</lastmod><priority>0.9</priority></url>' . "\n";
}
foreach (db()->query('SELECT slug FROM categories ORDER BY id') as $c) {
    echo '<url><loc>' . e(abs_url('/search?c=' . $c['slug'])) . '</loc><priority>0.6</priority></url>' . "\n";
}
foreach (db()->query('SELECT id, name FROM providers ORDER BY id') as $p) {
    echo '<url><loc>' . e(abs_url('/provider/' . $p['id'] . '-' . slugify($p['name']))) . '</loc><priority>0.4</priority></url>' . "\n";
}
echo '</urlset>';

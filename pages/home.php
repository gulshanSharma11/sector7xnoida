<?php
$societies = db()->query(
    'SELECT s.id, s.name, s.slug,
            COUNT(DISTINCT sp.provider_id) AS providers,
            COUNT(DISTINCT sp.category_id) AS cats
     FROM societies s
     LEFT JOIN society_providers sp ON sp.society_id = s.id
     GROUP BY s.id, s.name, s.slug
     ORDER BY s.name'
)->fetchAll();

$totalProviders = (int) db()->query('SELECT COUNT(*) FROM providers')->fetchColumn();
$totalCats = (int) db()->query('SELECT COUNT(DISTINCT category_id) FROM society_providers')->fetchColumn();

$topCats = db()->query(
    'SELECT c.name, c.slug, c.icon, COUNT(DISTINCT sp.provider_id) AS n
     FROM categories c JOIN society_providers sp ON sp.category_id = c.id
     GROUP BY c.id, c.name, c.slug, c.icon
     ORDER BY n DESC, c.name LIMIT 12'
)->fetchAll();

$lastSociety = remembered_society();

render('home_view', [
    'title'  => 'Sector7xNoida - Trusted local numbers for your society in Noida',
    'desc'   => 'Find doctors, pandit ji, RO technicians, plumbers, milkmen, drivers and more for Mahagun Moderne, Hyde Park and ' . (count($societies) - 2) . '+ Noida societies. All queries answered at one place.',
    'jsonld' => [[
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => cfg('site', 'name'),
        'url'      => abs_url('/'),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => abs_url('/search?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ]],
    'societies' => $societies, 'totalProviders' => $totalProviders, 'totalCats' => $totalCats, 'topCats' => $topCats,
    'lastSociety' => $lastSociety,
]);

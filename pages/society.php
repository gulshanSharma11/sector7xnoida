<?php
$slug = (string) ($_GET['slug'] ?? '');
$st = db()->prepare('SELECT id, name, slug FROM societies WHERE slug = ? OR legacy_slug = ? LIMIT 1');
$st->execute([$slug, $slug]);
$society = $st->fetch();
if (!$society) not_found();
// old links (e.g. /society/mahaguna-moderne) -> clean URL
if ($society['slug'] !== $slug) redirect(url('/society/' . $society['slug']), 301);

$rows = db()->prepare(
    'SELECT sp.category_id, p.id, p.name, p.address, p.specialization, p.website, p.priority
     FROM society_providers sp JOIN providers p ON p.id = sp.provider_id
     WHERE sp.society_id = ?
     ORDER BY sp.category_id, p.priority, p.name'
);
$rows->execute([$society['id']]);
$rows = $rows->fetchAll();

$ph = db()->prepare(
    'SELECT pp.* FROM provider_phones pp
     WHERE pp.provider_id IN (SELECT provider_id FROM society_providers WHERE society_id = ?)
     ORDER BY pp.provider_id, pp.sort'
);
$ph->execute([$society['id']]);
$phones = group_phones($ph->fetchAll());
$ratings = ratings_for(array_column($rows, 'id'));

$cats = [];
foreach (db()->query('SELECT id, name, slug, icon FROM categories ORDER BY id') as $c) $cats[(int) $c['id']] = $c;

$groups = [];
foreach ($rows as $r) $groups[(int) $r['category_id']][] = $r;
ksort($groups);

$total = count($rows);
$title = $society['name'] . ' Noida - Local Contacts: Doctors, Pandit, RO, Plumber & More | ' . cfg('site', 'name');
$desc  = 'Phone numbers of ' . $total . ' local services for ' . $society['name'] . ', Noida: doctors, pandit ji, RO technician, plumber, milkman, driver and more, in ' . count($groups) . ' categories.';

render('society_view', [
    'title' => $title, 'desc' => $desc,
    'society' => $society, 'groups' => $groups, 'cats' => $cats, 'phones' => $phones, 'ratings' => $ratings, 'total' => $total,
    'jsonld' => [[
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => abs_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $society['name'], 'item' => abs_url('/society/' . $society['slug'])],
        ],
    ]],
    'canonical' => abs_url('/society/' . $society['slug']),
]);

<?php
$q    = trim((string) ($_GET['q'] ?? ''));
$q    = mb_substr(preg_replace('/\s+/u', ' ', $q), 0, 80);
$cslug = (string) ($_GET['c'] ?? '');
$sslug = (string) ($_GET['s'] ?? '');
$page  = max(1, (int) ($_GET['p'] ?? 1));
$per   = 24;

$societies  = db()->query('SELECT id, name, slug FROM societies ORDER BY name')->fetchAll();
$categories = db()->query('SELECT id, name, slug, icon FROM categories ORDER BY name')->fetchAll();

$catId = null; $socId = null; $catRow = null; $socRow = null;
foreach ($categories as $c) if ($c['slug'] === $cslug) { $catId = (int) $c['id']; $catRow = $c; }
foreach ($societies as $s)  if ($s['slug'] === $sslug)  { $socId = (int) $s['id'];  $socRow = $s; }

$where = []; $args = [];
foreach (array_slice(array_filter(explode(' ', $q), 'strlen'), 0, 5) as $i => $w) {
    $like = '%' . addcslashes($w, '%_\\') . '%';
    $where[] = "(p.name LIKE :w{$i}a OR p.specialization LIKE :w{$i}b OR p.address LIKE :w{$i}c OR c.name LIKE :w{$i}d)";
    foreach (['a', 'b', 'c', 'd'] as $sfx) $args[":w{$i}{$sfx}"] = $like;
}
if ($catId) { $where[] = 'sp.category_id = :cat'; $args[':cat'] = $catId; }
if ($socId) { $where[] = 'sp.society_id = :soc'; $args[':soc'] = $socId; }

$searched = ($q !== '' || $catId || $socId);
$results = []; $phones = []; $ratings = []; $total = 0;

if ($searched) {
    $from = 'FROM providers p
             JOIN society_providers sp ON sp.provider_id = p.id
             JOIN categories c ON c.id = sp.category_id
             JOIN societies s ON s.id = sp.society_id';
    $w = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $cnt = db()->prepare("SELECT COUNT(DISTINCT p.id) $from $w");
    $cnt->execute($args);
    $total = (int) $cnt->fetchColumn();

    $off = ($page - 1) * $per;
    $st = db()->prepare(
        "SELECT p.id, p.name, p.address, p.specialization, p.website, p.priority,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.id SEPARATOR '||') AS cat_names,
                COUNT(DISTINCT sp.society_id) AS soc_count
         $from $w
         GROUP BY p.id, p.name, p.address, p.specialization, p.website, p.priority
         ORDER BY p.priority, p.name
         LIMIT $per OFFSET $off"
    );
    $st->execute($args);
    $results = $st->fetchAll();

    if ($results) {
        $ids = array_map('intval', array_column($results, 'id'));
        $in  = implode(',', $ids);
        $phones = group_phones(db()->query("SELECT * FROM provider_phones WHERE provider_id IN ($in) ORDER BY provider_id, sort")->fetchAll());
        $ratings = ratings_for($ids);
    }
}

$pages = (int) ceil($total / $per);
$label = trim(($q !== '' ? '"' . $q . '"' : '') . ($catRow ? ($q !== '' ? ' in ' : '') . $catRow['name'] : '') . ($socRow ? ' - ' . $socRow['name'] : ''));
$title = $searched && $label !== '' ? $label . ' - Local contacts in Noida | ' . cfg('site', 'name') : 'Search local services in Noida | ' . cfg('site', 'name');
$desc  = $catRow ? 'Find ' . $catRow['name'] . ' contacts for Noida societies.' : 'Search doctors, pandit ji, RO technicians, plumbers and other local services for your Noida society.';

render('search_view', compact('q', 'cslug', 'sslug', 'societies', 'categories', 'results', 'phones', 'ratings', 'total', 'page', 'pages', 'searched', 'label', 'socRow', 'catRow') + [
    'title' => $title, 'desc' => $desc,
    // search result pages with filters shouldn't compete with the main pages in Google
    'robots' => ($q !== '' || $socId) ? 'noindex,follow' : 'index,follow',
]);

<?php
$id = (int) ($_GET['id'] ?? 0);
$st = db()->prepare('SELECT link_url FROM ads WHERE id = ? AND active = 1');
$st->execute([$id]);
$link = $st->fetchColumn();
if ($link) {
    db()->prepare('UPDATE ads SET clicks = clicks + 1 WHERE id = ?')->execute([$id]);
    $href = preg_match('~^https?://~i', $link) ? $link : 'https://' . $link;
    redirect($href);
}
redirect(url('/'));

<?php
/** @var string $q $cslug $sslug @var array $societies $categories $results $phones @var int $total $page $pages @var bool $searched */
$qs = function (array $over = []) use ($q, $cslug, $sslug) {
    $a = array_filter(['q' => $q, 'c' => $cslug, 's' => $sslug] + [], fn($v) => $v !== '' && $v !== null);
    foreach ($over as $k => $v) { if ($v === null) unset($a[$k]); else $a[$k] = $v; }
    return url('/search') . ($a ? '?' . http_build_query($a) : '');
};
?>
<section class="page-head">
  <div class="wrap">
    <h1>Search local services</h1>
    <form class="filters" action="<?= e(url('/search')) ?>" method="get" role="search">
      <div class="f f-q">
        <label for="q">What do you need?</label>
        <input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="doctor, plumber, RO, pandit..." autocomplete="off">
      </div>
      <div class="f">
        <label for="c">Category</label>
        <select id="c" name="c">
          <option value="">All categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['slug']) ?>" <?= $c['slug'] === $cslug ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="f">
        <label for="s">Society</label>
        <select id="s" name="s">
          <option value="">All societies</option>
          <?php foreach ($societies as $s): ?>
            <option value="<?= e($s['slug']) ?>" <?= $s['slug'] === $sslug ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Search</button>
    </form>
  </div>
</section>

<div class="wrap listing">
<?php if (!can_see_contact() && $searched): ?>
  <p class="notice">Please <a href="<?= e(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>">login with your mobile number</a> to view phone numbers.</p>
<?php endif; ?>

<?php if (!$searched): ?>
  <p class="muted pad">Type a service above, or browse by category:</p>
  <div class="cat-grid">
    <?php foreach ($categories as $c): ?>
      <a class="cat" href="<?= e(url('/search?c=' . $c['slug'])) ?>"><span class="cat-ico" aria-hidden="true"><?= e($c['icon']) ?></span><span class="cat-name"><?= e($c['name']) ?></span></a>
    <?php endforeach; ?>
  </div>
<?php elseif (!$results): ?>
  <div class="empty-state">
    <h2>No results found</h2>
    <p class="muted">Try a shorter word like "doctor" or remove the society/category filter.</p>
    <a class="btn btn-primary" href="<?= e(url('/search')) ?>">Clear search</a>
  </div>
<?php else: ?>
  <p class="result-count"><strong><?= $total ?></strong> <?= $total === 1 ? 'contact' : 'contacts' ?> found<?= $label !== '' ? ' for ' . e($label) : '' ?></p>
  <div class="pgrid">
    <?php foreach ($results as $p):
        echo provider_card($p, $phones[(int) $p['id']] ?? [], $ratings[(int) $p['id']] ?? null);
    endforeach; ?>
  </div>
  <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pages">
      <?php if ($page > 1): ?><a class="btn btn-ghost" href="<?= e($qs(['p' => $page - 1])) ?>">&larr; Previous</a><?php endif; ?>
      <span>Page <?= $page ?> of <?= $pages ?></span>
      <?php if ($page < $pages): ?><a class="btn btn-ghost" href="<?= e($qs(['p' => $page + 1])) ?>">Next &rarr;</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
</div>

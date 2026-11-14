<?php /** @var array $society $groups $cats $phones $ratings @var int $total */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= e(url('/')) ?>">Home</a><span>/</span><a href="<?= e(url('/#societies')) ?>">Societies</a><span>/</span><span aria-current="page"><?= e($society['name']) ?></span></nav>
    <h1><?= e($society['name']) ?></h1>
    <p class="muted"><?= plural($total, 'contact', 'contacts') ?> in <?= plural(count($groups), 'category', 'categories') ?>. Tap a number to call, or WhatsApp where available.</p>
    <?php if (!can_see_contact()): ?>
      <p class="notice">Please <a href="<?= e(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>">login with your mobile number</a> to view phone numbers.</p>
    <?php endif; ?>
    <div class="toolbar">
      <label class="sr" for="pfilter">Search in <?= e($society['name']) ?></label>
      <input id="pfilter" class="search-in" type="search" placeholder="Search name, speciality or area in <?= e($society['name']) ?>..." autocomplete="off" data-provider-filter>
    </div>
  </div>
  <div class="chips-wrap">
    <div class="wrap">
      <div class="chips" role="tablist" aria-label="Categories">
        <button type="button" class="chip is-on" data-cat="all">All <small><?= $total ?></small></button>
        <?php foreach ($groups as $cid => $list): $c = $cats[$cid] ?? null; if (!$c) continue; ?>
          <button type="button" class="chip" data-cat="c<?= (int) $cid ?>"><?= e($c['icon']) ?> <?= e($c['name']) ?> <small><?= count($list) ?></small></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<div class="wrap listing">
  <?php foreach ($groups as $cid => $list): $c = $cats[$cid] ?? null; if (!$c) continue; ?>
    <section class="cat-section" id="c<?= (int) $cid ?>" data-cat-section="c<?= (int) $cid ?>">
      <h2><span class="ico" aria-hidden="true"><?= e($c['icon']) ?></span><span class="t"><?= e($c['name']) ?></span><small><?= count($list) ?></small></h2>
      <div class="pgrid">
        <?php foreach ($list as $p): echo provider_card($p, $phones[(int) $p['id']] ?? [], $ratings[(int) $p['id']] ?? null); endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <p class="empty" id="pempty" hidden>No contact matches your search in this society.</p>
</div>

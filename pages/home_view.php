<?php /** @var array $societies @var int $totalProviders @var int $totalCats @var array $topCats */ ?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <p class="eyebrow">Noida society directory</p>
      <h1>Every local number your society needs, <span>in one place.</span></h1>
      <p class="lead">Doctors, pandit ji, RO technicians, plumbers, milkmen, drivers and more. Pick your society and call in one tap.</p>
      <form class="hero-search" action="<?= e(url('/search')) ?>" method="get" role="search">
        <label class="sr" for="hq">Search a service</label>
        <input id="hq" type="search" name="q" placeholder="Try: doctor, plumber, RO, pandit..." autocomplete="off">
        <button class="btn btn-accent" type="submit">Search</button>
      </form>
      <dl class="stats">
        <div><dt><?= count($societies) ?></dt><dd>Societies</dd></div>
        <div><dt><?= $totalProviders ?></dt><dd>Contacts</dd></div>
        <div><dt><?= $totalCats ?></dt><dd>Categories</dd></div>
      </dl>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="phone-card">
        <div class="pc-head"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
        <div class="pc-row"><i>🩺</i><div><b>Doctors</b><small>Tap to call</small></div><em>Call</em></div>
        <div class="pc-row"><i>🪔</i><div><b>Pandit Ji</b><small>Puja &amp; Katha</small></div><em>Call</em></div>
        <div class="pc-row"><i>💧</i><div><b>RO Technician</b><small>Service &amp; repair</small></div><em class="wa">WhatsApp</em></div>
        <div class="pc-row"><i>🥛</i><div><b>Milkman</b><small>Daily delivery</small></div><em>Call</em></div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="societies">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>Choose your society</h2>
        <p class="muted">Each society page lists contacts category by category.</p>
      </div>
      <div class="filterbox">
        <label class="sr" for="socfilter">Filter societies</label>
        <input id="socfilter" type="search" placeholder="Type society name..." autocomplete="off" data-filter-target="#socgrid .soc">
      </div>
    </div>
    <div class="soc-grid" id="socgrid">
      <?php foreach ($societies as $s): ?>
        <a class="soc" href="<?= e(url('/society/' . $s['slug'])) ?>" data-search="<?= e(mb_strtolower($s['name'])) ?>">
          <span class="soc-initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($s['name'], 0, 1))) ?></span>
          <span class="soc-name"><?= e($s['name']) ?></span>
          <span class="soc-meta"><?= plural((int) $s['providers'], 'contact', 'contacts') ?> &middot; <?= plural((int) $s['cats'], 'category', 'categories') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="empty" id="socempty" hidden>No society matches that name.</p>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head"><div><h2>Popular services</h2><p class="muted">Jump straight to what you need across all societies.</p></div></div>
    <div class="cat-grid">
      <?php foreach ($topCats as $c): ?>
        <a class="cat" href="<?= e(url('/search?c=' . $c['slug'])) ?>">
          <span class="cat-ico" aria-hidden="true"><?= e($c['icon']) ?></span>
          <span class="cat-name"><?= e($c['name']) ?></span>
          <span class="cat-n"><?= (int) $c['n'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

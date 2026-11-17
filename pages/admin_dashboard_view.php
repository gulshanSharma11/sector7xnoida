<?php /** @var array $counts $recentReviews @var ?array $society */ ?>
<div class="admin-head">
  <div>
    <h1>Dashboard</h1>
    <p class="muted"><?= $society ? 'Overview of ' . e($society['name']) . '.' : 'Quick overview of your directory.' ?></p>
  </div>
</div>

<div class="admin-stats">
  <?php if (!$society): ?>
    <div class="admin-stat"><b><?= $counts['societies'] ?></b><span>Societies</span></div>
    <div class="admin-stat"><b><?= $counts['categories'] ?></b><span>Categories</span></div>
    <div class="admin-stat"><b><?= $counts['providers'] ?></b><span>Providers</span></div>
    <div class="admin-stat"><b><?= $counts['phones'] ?></b><span>Phone numbers</span></div>
    <div class="admin-stat"><b><?= $counts['reviews'] ?></b><span>Reviews</span></div>
    <div class="admin-stat"><b><?= $counts['users'] ?></b><span>Logged-in users</span></div>
  <?php else: ?>
    <div class="admin-stat"><b><?= $counts['providers'] ?></b><span>Providers in <?= e($society['name']) ?></span></div>
    <div class="admin-stat"><b><?= $counts['categories'] ?></b><span>Categories used</span></div>
    <div class="admin-stat"><b><?= $counts['reviews'] ?></b><span>Reviews</span></div>
  <?php endif; ?>
</div>

<?php if ($society): ?>
<div class="admin-card">
  <p class="muted">You're a sub admin for <b><?= e($society['name']) ?></b>. You can add new providers and update existing ones in this society, and moderate its reviews. Deleting anything, managing other societies, or editing categories needs a super admin.</p>
  <a class="btn btn-primary" href="<?= e(admin_url('providers')) ?>">Manage providers &rarr;</a>
</div>
<?php endif; ?>

<div class="admin-card">
  <h2>Recent reviews</h2>
  <?php if (!$recentReviews): ?>
    <p class="admin-empty">No reviews yet.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Provider</th><th>Rating</th><th>Comment</th><th>By</th><th>When</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentReviews as $r): ?>
          <tr>
            <td><a href="<?= e(admin_url('providers/' . $r['provider_id'])) ?>"><?= e($r['provider_name']) ?></a></td>
            <td><?= stars_html((float) $r['rating']) ?> <?php if ($r['pinned']): ?><span class="badge badge-pin">Top</span><?php endif; ?></td>
            <td><?= e(mb_strimwidth((string) $r['comment'], 0, 60, '…')) ?></td>
            <td class="muted">+<?= e($r['country_code']) ?> <?= e(mask_mobile($r['mobile'])) ?></td>
            <td class="muted"><?= e(date('d M', strtotime($r['updated_at']))) ?></td>
            <td><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('reviews')) ?>">Manage</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

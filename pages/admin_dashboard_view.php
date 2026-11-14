<?php /** @var array $counts $recentReviews */ ?>
<div class="admin-head">
  <div><h1>Dashboard</h1><p class="muted">Quick overview of your directory.</p></div>
</div>

<div class="admin-stats">
  <div class="admin-stat"><b><?= $counts['societies'] ?></b><span>Societies</span></div>
  <div class="admin-stat"><b><?= $counts['categories'] ?></b><span>Categories</span></div>
  <div class="admin-stat"><b><?= $counts['providers'] ?></b><span>Providers</span></div>
  <div class="admin-stat"><b><?= $counts['phones'] ?></b><span>Phone numbers</span></div>
  <div class="admin-stat"><b><?= $counts['reviews'] ?></b><span>Reviews</span></div>
  <div class="admin-stat"><b><?= $counts['users'] ?></b><span>Logged-in users</span></div>
</div>

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

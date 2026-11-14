<?php /** @var array $reviews @var string $error $success $q @var int $min */ ?>
<div class="admin-head">
  <div><h1>Reviews &amp; ratings</h1><p class="muted">Pin a review to show it on top of a provider's public page, edit or remove any review.</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <form method="get" action="<?= e(admin_url('reviews')) ?>" class="admin-search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search provider, comment or mobile...">
    <select name="min" style="max-width:160px">
      <option value="0" <?= $min === 0 ? 'selected' : '' ?>>Any rating</option>
      <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= $min === $i ? 'selected' : '' ?>><?= $i ?>★ &amp; up</option><?php endfor; ?>
    </select>
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
    <?php if ($q !== '' || $min): ?><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('reviews')) ?>">Clear</a><?php endif; ?>
  </form>

  <?php if (!$reviews): ?>
    <p class="admin-empty">No reviews match.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Provider</th><th>Rating</th><th>Comment</th><th>By</th><th>When</th><th></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
          <tr>
            <td><a href="<?= e(admin_url('providers/' . $r['provider_id'])) ?>"><?= e($r['provider_name']) ?></a></td>
            <td>
              <form method="post" action="<?= e(admin_url('reviews')) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <select name="rating" style="width:70px;display:inline-block">
                  <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int) $r['rating'] === $i ? 'selected' : '' ?>><?= $i ?>★</option><?php endfor; ?>
                </select>
            </td>
            <td style="min-width:220px">
                <input type="text" name="comment" value="<?= e($r['comment']) ?>" style="width:100%">
                <button class="btn btn-ghost btn-sm" type="submit" style="margin-top:6px">Save edit</button>
              </form>
            </td>
            <td class="muted">+<?= e($r['country_code']) ?> <?= e(mask_mobile($r['mobile'])) ?></td>
            <td class="muted"><?= e(date('d M Y', strtotime($r['updated_at']))) ?></td>
            <td>
              <?php if ($r['pinned']): ?>
                <form method="post" action="<?= e(admin_url('reviews')) ?>" class="inline-form">
                  <?= csrf_field() ?><input type="hidden" name="action" value="unpin"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <button class="btn btn-sm" style="background:var(--primary);color:#fff" type="submit">★ On top</button>
                </form>
              <?php else: ?>
                <form method="post" action="<?= e(admin_url('reviews')) ?>" class="inline-form">
                  <?= csrf_field() ?><input type="hidden" name="action" value="pin"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <button class="btn btn-ghost btn-sm" type="submit">Pin to top</button>
                </form>
              <?php endif; ?>
            </td>
            <td class="rowacts">
              <form method="post" action="<?= e(admin_url('reviews')) ?>" class="inline-form" onsubmit="return confirm('Delete this review?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

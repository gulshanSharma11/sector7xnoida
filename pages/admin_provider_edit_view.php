<?php /** @var array $provider $phones $links $allSocieties $allCategories $reviews @var ?array $rating @var string $error $success */ ?>
<p class="admin-crumb"><a href="<?= e(admin_url('providers')) ?>">&larr; All providers</a></p>
<div class="admin-head">
  <div><h1><?= e($provider['name']) ?></h1><p class="muted"><?= rating_summary($rating) ?></p></div>
  <a class="btn btn-ghost btn-sm" href="<?= e(provider_url($provider)) ?>" target="_blank" rel="noopener">View on site &rarr;</a>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Details</h2>
  <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" class="admin-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="save_details">
    <div class="row">
      <div><label for="name">Name</label><input id="name" name="name" type="text" value="<?= e($provider['name']) ?>" required></div>
      <div><label for="specialization">Specialization</label><input id="specialization" name="specialization" type="text" value="<?= e($provider['specialization']) ?>" placeholder="e.g. General Physician"></div>
    </div>
    <div class="row">
      <div><label for="address">Address</label><input id="address" name="address" type="text" value="<?= e($provider['address']) ?>"></div>
      <div><label for="website">Website</label><input id="website" name="website" type="text" value="<?= e($provider['website']) ?>" placeholder="https://..."></div>
    </div>
    <label class="check"><input type="checkbox" name="is_new" <?= (int) $provider['priority'] === 1 ? 'checked' : '' ?>> Show a "New" badge on this listing</label>
    <div class="actions"><button class="btn btn-primary" type="submit">Save details</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>Phone numbers</h2>
  <?php if ($phones): ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Display</th><th>Tel link</th><th>WhatsApp</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($phones as $ph): ?>
          <tr>
            <td><?= e($ph['display']) ?></td>
            <td class="muted"><?= e($ph['tel']) ?></td>
            <td class="muted"><?= $ph['whatsapp'] ? 'Yes' : 'No' ?></td>
            <td class="rowacts">
              <?php if (is_super_admin()): ?>
              <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" class="inline-form" onsubmit="return confirm('Remove this number?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete_phone"><input type="hidden" name="phone_id" value="<?= (int) $ph['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="admin-empty">No phone numbers yet.</p>
  <?php endif; ?>
  <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" class="admin-form" style="margin-top:14px">
    <?= csrf_field() ?><input type="hidden" name="action" value="add_phone">
    <div class="row">
      <div><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" placeholder="e.g. 9876543210 or 0120-4567890" required></div>
      <div><label for="display">Display text (optional)</label><input id="display" name="display" type="text" placeholder="Leave blank to use the number"></div>
    </div>
    <p class="small muted">10-digit numbers starting 6-9 are treated as mobile (get a WhatsApp button automatically). Anything else is treated as a landline.</p>
    <div class="actions"><button class="btn btn-primary" type="submit">Add number</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>Listed under (society + category)</h2>
  <?php if ($links): ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Society</th><th>Category</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($links as $l): ?>
          <tr>
            <td><?= e($l['society_name']) ?></td>
            <td class="muted"><?= e($l['category_name']) ?></td>
            <td class="rowacts">
              <?php if (is_super_admin()): ?>
              <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete_link">
                <input type="hidden" name="society_id" value="<?= (int) $l['society_id'] ?>"><input type="hidden" name="category_id" value="<?= (int) $l['category_id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="admin-empty">Not listed under any society yet.</p>
  <?php endif; ?>
  <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" class="admin-form" style="margin-top:14px">
    <?= csrf_field() ?><input type="hidden" name="action" value="add_link">
    <div class="row">
      <?php if (is_super_admin()): ?>
      <div><label for="society_id">Society</label>
        <select id="society_id" name="society_id" required>
          <option value="">Choose a society...</option>
          <?php foreach ($allSocieties as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div><label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
          <option value="">Choose a category...</option>
          <?php foreach ($allCategories as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Add listing</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>Reviews for this provider</h2>
  <?php if (!$reviews): ?>
    <p class="admin-empty">No reviews yet.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Rating</th><th>Comment</th><th>By</th><th>When</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
          <tr>
            <td><?= stars_html((float) $r['rating']) ?></td>
            <td><?= e($r['comment']) ?></td>
            <td class="muted">+<?= e($r['country_code']) ?> <?= e(mask_mobile($r['mobile'])) ?></td>
            <td class="muted"><?= e(date('d M Y', strtotime($r['updated_at']))) ?></td>
            <td class="rowacts"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('reviews')) ?>">Manage</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php if (is_super_admin()): ?>
<div class="admin-card">
  <h2>Delete this provider</h2>
  <p class="muted">Removes it everywhere (phone numbers, society listings and reviews are removed too). This cannot be undone.</p>
  <form method="post" action="<?= e(admin_url('providers/' . $provider['id'])) ?>" onsubmit="return confirm('Permanently delete this provider and all its data?')">
    <?= csrf_field() ?><input type="hidden" name="action" value="delete_provider">
    <button class="btn btn-danger" type="submit">Delete provider</button>
  </form>
</div>
<?php endif; ?>

<?php /** @var array $categories @var string $error $success */ ?>
<div class="admin-head">
  <div><h1>Categories</h1><p class="muted"><?= count($categories) ?> categories (e.g. Plumber, Doctor, Milk Delivery).</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Add a category</h2>
  <form method="post" action="<?= e(admin_url('categories')) ?>" class="admin-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label for="cname">Name</label><input id="cname" name="name" type="text" required placeholder="e.g. Plumber"></div>
      <div><label for="cicon">Icon (one emoji, optional)</label><input id="cicon" name="icon" type="text" maxlength="8" placeholder="🔧"></div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Add category</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>All categories</h2>
  <?php if (!$categories): ?>
    <p class="admin-empty">No categories yet.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Icon &amp; name</th><th>Slug</th><th class="num">Listings</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td>
              <form method="post" action="<?= e(admin_url('categories')) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <input type="text" name="icon" value="<?= e($c['icon']) ?>" maxlength="8" style="width:52px;display:inline-block">
                <input type="text" name="name" value="<?= e($c['name']) ?>" style="min-width:200px;display:inline-block;width:auto">
                <button class="btn btn-ghost btn-sm" type="submit">Save</button>
              </form>
            </td>
            <td class="muted"><?= e($c['slug']) ?></td>
            <td class="num"><?= (int) $c['link_count'] ?></td>
            <td class="rowacts">
              <form method="post" action="<?= e(admin_url('categories')) ?>" class="inline-form" onsubmit="return confirm('Delete this category? Listings using it will be removed.')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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

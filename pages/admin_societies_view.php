<?php /** @var array $societies @var string $error $success $q */ ?>
<div class="admin-head">
  <div><h1>Societies</h1><p class="muted"><?= count($societies) ?> listed.</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Add a society</h2>
  <form method="post" action="<?= e(admin_url('societies')) ?>" class="admin-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label for="name">Society name</label><input id="name" name="name" type="text" required placeholder="e.g. Prateek Wisteria"></div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Add society</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>All societies</h2>
  <form method="get" action="<?= e(admin_url('societies')) ?>" class="admin-search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search societies...">
    <button class="btn btn-ghost btn-sm" type="submit">Search</button>
    <?php if ($q !== ''): ?><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('societies')) ?>">Clear</a><?php endif; ?>
  </form>

  <?php if (!$societies): ?>
    <p class="admin-empty">No societies found.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Name</th><th>Slug</th><th class="num">Providers</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($societies as $s): ?>
          <tr>
            <td>
              <form method="post" action="<?= e(admin_url('societies')) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="rename"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="text" name="name" value="<?= e($s['name']) ?>" style="min-width:220px;display:inline-block;width:auto">
                <button class="btn btn-ghost btn-sm" type="submit">Save</button>
              </form>
            </td>
            <td class="muted"><?= e($s['slug']) ?></td>
            <td class="num"><a href="<?= e(url('/society/' . $s['slug'])) ?>" target="_blank" rel="noopener"><?= (int) $s['provider_count'] ?></a></td>
            <td class="rowacts">
              <form method="post" action="<?= e(admin_url('societies')) ?>" class="inline-form" onsubmit="return confirm('Delete this society? Its provider listings under it will be removed too (providers themselves stay).')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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

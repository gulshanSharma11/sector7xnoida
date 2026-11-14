<?php /** @var array $providers $ratings @var string $error $success $q @var int $page $perPage $total */ ?>
<div class="admin-head">
  <div><h1>Providers</h1><p class="muted"><?= $total ?> total — doctors, plumbers, pandits, milkmen, etc.</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Add a provider</h2>
  <form method="post" action="<?= e(admin_url('providers')) ?>" class="admin-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label for="pname">Name</label><input id="pname" name="name" type="text" required placeholder="e.g. Dr. Sharma Clinic"></div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Add &amp; continue editing</button></div>
    <p class="small muted">You'll add phone numbers, address, society/category and other details next.</p>
  </form>
</div>

<div class="admin-card">
  <h2>All providers</h2>
  <form method="get" action="<?= e(admin_url('providers')) ?>" class="admin-search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name or specialization...">
    <button class="btn btn-ghost btn-sm" type="submit">Search</button>
    <?php if ($q !== ''): ?><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('providers')) ?>">Clear</a><?php endif; ?>
  </form>

  <?php if (!$providers): ?>
    <p class="admin-empty">No providers found.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Name</th><th>Specialization</th><th>Rating</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($providers as $p):
          $r = $ratings[(int) $p['id']] ?? null; ?>
          <tr>
            <td><a href="<?= e(admin_url('providers/' . $p['id'])) ?>"><b><?= e($p['name']) ?></b></a> <?php if ((int) $p['priority'] === 1): ?><span class="badge badge-new">New</span><?php endif; ?></td>
            <td class="muted"><?= e($p['specialization']) ?></td>
            <td><?= rating_summary($r) ?></td>
            <td class="rowacts"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('providers/' . $p['id'])) ?>">Edit</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php $pages = (int) ceil($total / $perPage); if ($pages > 1): ?>
      <div class="pagelinks">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
          <?php else: ?><a href="<?= e(admin_url('providers') . '?page=' . $i . ($q !== '' ? '&q=' . rawurlencode($q) : '')) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

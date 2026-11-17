<?php /** @var array $admins $societies @var string $error $success */
$meId = (int) (current_admin()['id'] ?? 0);
?>
<div class="admin-head">
  <div><h1>Admin accounts</h1><p class="muted">Super admins have full access. Sub admins can only add/update providers &amp; reviews for the one society they're assigned to - never delete, never other societies.</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Add an admin</h2>
  <form method="post" action="<?= e(admin_url('admins')) ?>" class="admin-form" id="add-admin-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label for="username">Username</label><input id="username" name="username" type="text" required autocomplete="off"></div>
      <div><label for="password">Password</label><input id="password" name="password" type="text" required minlength="6" placeholder="min. 6 characters"></div>
    </div>
    <div class="row">
      <div><label for="role">Role</label>
        <select id="role" name="role" onchange="document.getElementById('society-row').style.display = this.value === 'sub' ? '' : 'none'">
          <option value="sub">Sub admin (one society only)</option>
          <option value="super">Super admin (full access)</option>
        </select>
      </div>
      <div id="society-row"><label for="society_id">Society (sub admin only)</label>
        <select id="society_id" name="society_id">
          <option value="">Choose a society...</option>
          <?php foreach ($societies as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Create account</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>All admins</h2>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Username</th><th>Role</th><th>Society</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($admins as $a): ?>
        <tr>
          <td><b><?= e($a['username']) ?></b><?php if ((int) $a['id'] === $meId): ?> <span class="muted small">(you)</span><?php endif; ?></td>
          <td><span class="role-badge role-<?= e($a['role']) ?>" style="color:var(--ink)"><?= $a['role'] === 'super' ? 'Super admin' : 'Sub admin' ?></span></td>
          <td class="muted"><?= e($a['society_name'] ?? '') ?: '<span class="muted">&mdash;</span>' ?></td>
          <td><?php if ($a['active']): ?><span class="badge badge-pin">Active</span><?php else: ?><span class="badge badge-off">Off</span><?php endif; ?></td>
          <td class="rowacts">
            <details class="inline-form" style="display:inline-block">
              <summary class="btn btn-ghost btn-sm" style="cursor:pointer;display:inline-block">Reset password</summary>
              <form method="post" action="<?= e(admin_url('admins')) ?>" style="margin-top:8px;display:flex;gap:6px">
                <?= csrf_field() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <input type="text" name="password" placeholder="New password" minlength="6" required style="width:160px;padding:6px 10px;border:1.5px solid var(--line);border-radius:8px">
                <button class="btn btn-primary btn-sm" type="submit">Set</button>
              </form>
            </details>
            <?php if ((int) $a['id'] !== $meId): ?>
              <form method="post" action="<?= e(admin_url('admins')) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit"><?= $a['active'] ? 'Turn off' : 'Turn on' ?></button>
              </form>
              <form method="post" action="<?= e(admin_url('admins')) ?>" class="inline-form" onsubmit="return confirm('Delete this admin account?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

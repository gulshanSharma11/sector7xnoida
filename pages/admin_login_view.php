<?php /** @var string $error $next */ ?>
<section class="auth">
  <div class="wrap">
    <div class="auth-card admin-login-card">
      <h1>Admin login</h1>
      <p class="muted">Super admins manage everything. Sub admins manage only their assigned society.</p>
      <?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" action="<?= e(admin_url('login')) ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" autocomplete="username" required autofocus style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:10px;font:inherit;margin-bottom:16px">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:10px;font:inherit;margin-bottom:16px">
        <button class="btn btn-primary btn-block" type="submit">Login</button>
      </form>
    </div>
  </div>
</section>

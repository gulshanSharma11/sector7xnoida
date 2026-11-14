<?php /** @var string $step $error $info $mobile $method $next $cc */ ?>
<section class="auth">
  <div class="wrap">
    <div class="auth-card">
      <h1><?= $step === 'otp' ? 'Enter OTP' : 'Login with mobile' ?></h1>
      <p class="muted"><?= $step === 'otp' ? 'We sent a 4-digit code to +' . e($cc) . ' ' . e(mask_mobile($mobile)) . '.' : 'We will send a one-time password to verify your number.' ?></p>

      <?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?php if ($info): ?><div class="alert alert-ok" role="status"><?= e($info) ?></div><?php endif; ?>

      <?php if ($step === 'mobile'): ?>
        <form method="post" action="<?= e(url('/login')) ?>" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="send">
          <input type="hidden" name="next" value="<?= e($next) ?>">
          <label for="mobile">Mobile number</label>
          <div class="phone-in"><span>+<?= e($cc) ?></span><input id="mobile" name="mobile" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="98765 43210" required autofocus></div>
          <div class="methods" role="radiogroup" aria-labelledby="mlabel">
            <span id="mlabel" class="mlabel">Receive OTP on</span>
            <label><input type="radio" name="method" value="whatsapp" checked> WhatsApp</label>
            <label><input type="radio" name="method" value="sms"> SMS</label>
          </div>
          <button class="btn btn-primary btn-block" type="submit">Send OTP</button>
        </form>
      <?php else: ?>
        <form method="post" action="<?= e(url('/login')) ?>" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="verify">
          <input type="hidden" name="next" value="<?= e($next) ?>">
          <label for="otp">4-digit OTP</label>
          <input id="otp" class="otp-in" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="4" pattern="[0-9]{4}" placeholder="0000" required autofocus>
          <button class="btn btn-primary btn-block" type="submit">Verify &amp; login</button>
        </form>
        <form method="post" action="<?= e(url('/login')) ?>" class="inline-links">
          <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
          <button class="linkbtn" name="action" value="change" type="submit">Change number / resend OTP</button>
        </form>
      <?php endif; ?>
      <p class="small muted">By continuing you agree to be contacted on this number for login only.</p>
    </div>
  </div>
</section>

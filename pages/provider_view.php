<?php /** @var array $provider $listings $phones $reviews @var ?array $rating $myReview @var string $error $success */
$user = current_user();
$show = can_see_contact();
?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= e(url('/')) ?>">Home</a><span>/</span><a href="<?= e(url('/search')) ?>">Search</a><span>/</span><span aria-current="page"><?= e($provider['name']) ?></span></nav>
    <h1><?= e($provider['name']) ?></h1>
    <?php if ($provider['specialization'] !== ''): ?><p class="pspec" style="margin-top:6px"><?= e($provider['specialization']) ?></p><?php endif; ?>
    <div class="prov-rating-row">
      <?php if ($rating && $rating['count'] > 0): ?>
        <?= stars_html($rating['avg'], 'stars-lg') ?>
        <b class="prov-avg"><?= e(number_format($rating['avg'], 1)) ?></b>
        <span class="muted"><?= plural($rating['count'], 'review', 'reviews') ?></span>
      <?php else: ?>
        <span class="muted">No ratings yet — be the first to rate <?= e($provider['name']) ?>.</span>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="wrap listing provider-wrap">
  <div class="prov-grid">
    <div>
      <section class="prov-card">
        <h2>Contact</h2>
        <?php if ($provider['address'] !== ''): ?>
          <p class="paddr"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg><?= e($provider['address']) ?></p>
        <?php endif; ?>
        <div class="pphones">
          <?php if (!$phones): ?>
            <span class="nophone">No number listed</span>
          <?php elseif (!$show): ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>">Login to view number</a>
          <?php else: foreach ($phones as $ph): ?>
            <div class="prow">
              <a class="btn btn-call" href="tel:<?= e($ph['tel']) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><?= e($ph['display']) ?></a>
              <?php if (!empty($ph['whatsapp'])): ?><a class="btn btn-wa" target="_blank" rel="noopener" href="https://wa.me/<?= e($ph['whatsapp']) ?>">WhatsApp</a><?php endif; ?>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <?php if ($provider['website'] !== ''): $href = preg_match('~^https?://~i', $provider['website']) ? $provider['website'] : 'https://' . $provider['website']; ?>
          <a class="pweb" target="_blank" rel="noopener nofollow" href="<?= e($href) ?>">Visit website &rarr;</a>
        <?php endif; ?>
      </section>

      <?php if ($listings): ?>
      <section class="prov-card">
        <h2>Listed under</h2>
        <ul class="prov-listings">
          <?php foreach ($listings as $l): ?>
            <li><a href="<?= e(url('/society/' . $l['society_slug'])) ?>"><?= e($l['society_name']) ?></a> &middot; <span class="muted"><?= e($l['cat_icon']) ?> <?= e($l['cat_name']) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>

      <?= render_ad_rail() ?>
    </div>

    <div>
      <section class="prov-card review-form-card">
        <h2><?= $myReview ? 'Update your review' : 'Write a review' ?></h2>

        <?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

        <?php if (!$user): ?>
          <p class="muted">Only logged-in users can rate and review. <a href="<?= e(url('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'))) ?>">Login with your mobile number</a> to write one.</p>
        <?php else: ?>
          <form method="post" action="<?= e(provider_url($provider)) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <fieldset class="star-input">
              <legend>Tap a star to rate <?= e($provider['name']) ?></legend>
              <div class="star-input-row">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" id="r<?= $i ?>" name="rating" value="<?= $i ?>" <?= (($myReview['rating'] ?? 0) === $i) ? 'checked' : '' ?> required>
                  <label for="r<?= $i ?>" title="<?= $i ?> star<?= $i === 1 ? '' : 's' ?>">★</label>
                <?php endfor; ?>
              </div>
            </fieldset>
            <label for="comment">Your review (optional)</label>
            <textarea id="comment" name="comment" rows="4" maxlength="600" placeholder="How was your experience with <?= e($provider['name']) ?>?"><?= e($myReview['comment'] ?? '') ?></textarea>
            <button class="btn btn-primary btn-block" type="submit"><?= $myReview ? 'Update review' : 'Submit review' ?></button>
          </form>
          <?php if ($myReview): ?>
            <form method="post" action="<?= e(provider_url($provider)) ?>" class="inline-links">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete">
              <button class="linkbtn" type="submit" onclick="return confirm('Remove your review?')">Remove my review</button>
            </form>
          <?php endif; ?>
        <?php endif; ?>
      </section>

      <section class="prov-card">
        <h2>Reviews <small><?= count($reviews) ?></small></h2>
        <?php if (!$reviews): ?>
          <p class="muted">No reviews yet for <?= e($provider['name']) ?>.</p>
        <?php else: ?>
          <ul class="review-list">
            <?php foreach ($reviews as $r): ?>
              <li class="review-item">
                <div class="review-top">
                  <?= stars_html((float) $r['rating']) ?>
                  <span class="review-who">+<?= e($r['country_code']) ?> <?= e(mask_mobile($r['mobile'])) ?></span>
                  <time class="review-date muted"><?= e(date('d M Y', strtotime($r['updated_at']))) ?></time>
                  <?php if (!empty($r['pinned'])): ?><span class="badge badge-pin">Featured</span><?php endif; ?>
                </div>
                <?php if ($r['comment'] !== ''): ?><p class="review-comment"><?= e($r['comment']) ?></p><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>
  </div>
</div>

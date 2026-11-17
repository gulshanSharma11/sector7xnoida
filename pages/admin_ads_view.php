<?php /** @var array $ads @var string $error $success */ ?>
<div class="admin-head">
  <div><h1>Advertisements</h1><p class="muted"><?= count($ads) ?> total. "Rail" shows as a side banner on society &amp; provider pages. "Banner" shows as a wide strip on the home page.</p></div>
</div>

<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok" role="status"><?= e($success) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Add an ad</h2>
  <form method="post" action="<?= e(admin_url('ads')) ?>" class="admin-form" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label for="title">Title (internal label + image alt text)</label><input id="title" name="title" type="text" required placeholder="e.g. XYZ Paints - Diwali offer"></div>
      <div><label for="placement">Placement</label>
        <select id="placement" name="placement">
          <option value="rail">Sidebar rail (society &amp; provider pages)</option>
          <option value="banner">Horizontal banner (home page)</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div><label for="link_url">Where it links to</label><input id="link_url" name="link_url" type="text" placeholder="https://..."></div>
      <div><label for="image">Upload image or video (reel)</label><input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/webm,video/quicktime"></div>
    </div>
    <div><label for="image_url">...or paste an image/video URL instead of uploading</label><input id="image_url" name="image_url" type="text" placeholder="https://.../banner.jpg or .../reel.mp4"></div>
    <p class="small muted">Images: JPG/PNG/WEBP/GIF, max 3MB. Videos/reels: MP4/WEBM/MOV, max 25MB (plays muted &amp; on loop, no sound). Rail images/reels look best around 300&times;250px or 300&times;600px (portrait reels work well too). Banner images look best around 970&times;120px.</p>
    <div class="actions"><button class="btn btn-primary" type="submit">Add ad</button></div>
  </form>
</div>

<div class="admin-card">
  <h2>All ads</h2>
  <?php if (!$ads): ?>
    <p class="admin-empty">No ads yet.</p>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Image</th><th>Title</th><th>Placement</th><th class="num">Clicks</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($ads as $a):
          $img = preg_match('~^https?://~i', $a['image_url']) ? $a['image_url'] : ads_upload_url($a['image_url']); ?>
          <tr>
            <td>
              <?php if (ad_is_video($a['image_url'])): ?>
                <video class="admin-thumb" src="<?= e($img) ?>" muted></video>
              <?php else: ?>
                <img class="admin-thumb" src="<?= e($img) ?>" alt="">
              <?php endif; ?>
            </td>
            <td><b><?= e($a['title']) ?></b><?php if (ad_is_video($a['image_url'])): ?> <span class="badge badge-off">Video</span><?php endif; ?><br><span class="muted small"><?= e($a['link_url']) ?></span></td>
            <td class="muted"><?= $a['placement'] === 'banner' ? 'Banner' : 'Rail' ?></td>
            <td class="num"><?= (int) $a['clicks'] ?></td>
            <td><?php if ($a['active']): ?><span class="badge badge-pin">Active</span><?php else: ?><span class="badge badge-off">Off</span><?php endif; ?></td>
            <td class="rowacts">
              <form method="post" action="<?= e(admin_url('ads')) ?>" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit"><?= $a['active'] ? 'Turn off' : 'Turn on' ?></button>
              </form>
              <form method="post" action="<?= e(admin_url('ads')) ?>" class="inline-form" onsubmit="return confirm('Delete this ad?')">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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

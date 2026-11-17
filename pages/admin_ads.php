<?php
require_super_admin();

$error = '';
$success = '';

/** Accepts an image (JPG/PNG/WEBP/GIF, max 3MB) OR a short video/reel (MP4/WEBM/MOV, max 25MB). */
function ads_save_upload(string $field): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed. Please try again.');
    $tmp = $_FILES[$field]['tmp_name'];
    $size = (int) $_FILES[$field]['size'];

    $imageExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $info = @getimagesize($tmp);
    if ($info && isset($imageExt[$info['mime']])) {
        if ($size > 3 * 1024 * 1024) throw new RuntimeException('Image must be 3MB or smaller.');
        $ext = $imageExt[$info['mime']];
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp) ?: '';
        finfo_close($finfo);
        $videoExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
        if (!isset($videoExt[$mime])) throw new RuntimeException('Use a JPG/PNG/WEBP/GIF image, or an MP4/WEBM/MOV video (reel).');
        if ($size > 25 * 1024 * 1024) throw new RuntimeException('Video must be 25MB or smaller.');
        $ext = $videoExt[$mime];
    }

    $dir = ads_upload_dir();
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = 'ad-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) throw new RuntimeException('Could not save the uploaded file.');
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        try {
            if ($action === 'add') {
                $title = trim((string) ($_POST['title'] ?? ''));
                $link = trim((string) ($_POST['link_url'] ?? ''));
                $placement = ($_POST['placement'] ?? '') === 'banner' ? 'banner' : 'rail';
                $externalImage = trim((string) ($_POST['image_url'] ?? ''));
                $uploaded = ads_save_upload('image');
                $image = $uploaded ?? $externalImage;
                if ($title === '' || $image === '') {
                    $error = 'Title and an image (upload or image URL) are required.';
                } else {
                    db()->prepare('INSERT INTO ads (title, image_url, link_url, placement, active, sort, clicks, created_at) VALUES (?,?,?,?,1,0,0,NOW())')
                        ->execute([$title, $image, $link, $placement]);
                    redirect(admin_url('ads') . '?added=1');
                }
            } elseif ($action === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                db()->prepare('UPDATE ads SET active = 1 - active WHERE id = ?')->execute([$id]);
                redirect(admin_url('ads') . '?saved=1');
            } elseif ($action === 'sort') {
                $id = (int) ($_POST['id'] ?? 0);
                $sort = (int) ($_POST['sort'] ?? 0);
                db()->prepare('UPDATE ads SET sort = ? WHERE id = ?')->execute([$sort, $id]);
                redirect(admin_url('ads') . '?saved=1');
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $st = db()->prepare('SELECT image_url FROM ads WHERE id = ?');
                $st->execute([$id]);
                $img = $st->fetchColumn();
                db()->prepare('DELETE FROM ads WHERE id = ?')->execute([$id]);
                if ($img && !preg_match('~^https?://~i', $img)) {
                    $f = ads_upload_dir() . '/' . basename($img);
                    if (is_file($f)) @unlink($f);
                }
                redirect(admin_url('ads') . '?deleted=1');
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

if (isset($_GET['added'])) $success = 'Ad added.';
if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['deleted'])) $success = 'Ad deleted.';

$ads = db()->query('SELECT * FROM ads ORDER BY placement, sort, id DESC')->fetchAll();

render_admin('admin_ads_view', [
    'title' => 'Advertisements | Admin | ' . cfg('site', 'name'),
    'ads' => $ads, 'error' => $error, 'success' => $success,
]);

<?php
/** Admin login (super admin or sub admin), separate from the public mobile-OTP user session. */
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (!str_starts_with($next, admin_url(''))) $next = admin_url('');
if (is_admin()) redirect($next);

$error = '';

function admin_login_count(int $minutes): int
{
    $st = db()->prepare("SELECT COUNT(*) FROM login_log WHERE mobile = '__admin__' AND action = 'admin_login' AND ok = 0 AND ip = ? AND created_at > (NOW() - INTERVAL $minutes MINUTE)");
    $st->execute([client_ip()]);
    return (int) $st->fetchColumn();
}
function admin_login_log(bool $ok): void
{
    db()->prepare("INSERT INTO login_log (mobile, ip, action, ok, created_at) VALUES ('__admin__', ?, 'admin_login', ?, NOW())")
        ->execute([client_ip(), $ok ? 1 : 0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif (admin_login_count(15) >= 8) {
        $error = 'Too many wrong attempts. Please try again after 15 minutes.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $pw = (string) ($_POST['password'] ?? '');
        $st = db()->prepare('SELECT * FROM admins WHERE username = ? AND active = 1');
        $st->execute([$username]);
        $row = $st->fetch();
        if ($row && password_verify($pw, $row['password_hash'])) {
            admin_login_log(true);
            session_regenerate_id(true);
            $_SESSION['admin'] = [
                'id'         => (int) $row['id'],
                'username'   => $row['username'],
                'role'       => $row['role'],
                'society_id' => $row['society_id'] !== null ? (int) $row['society_id'] : null,
            ];
            redirect($next);
        }
        admin_login_log(false);
        $error = 'Wrong username or password.';
    }
}

render_admin_auth('admin_login_view', [
    'title' => 'Admin login | ' . cfg('site', 'name'),
    'error' => $error, 'next' => $next,
]);

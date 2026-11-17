<?php
require_super_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = ($_POST['role'] ?? '') === 'super' ? 'super' : 'sub';
            $societyId = (int) ($_POST['society_id'] ?? 0) ?: null;
            if ($username === '' || strlen($password) < 6) {
                $error = 'Username is required and password must be at least 6 characters.';
            } elseif ($role === 'sub' && !$societyId) {
                $error = 'Choose a society for this sub admin.';
            } else {
                $st = db()->prepare('SELECT COUNT(*) FROM admins WHERE username = ?');
                $st->execute([$username]);
                if ((int) $st->fetchColumn() > 0) {
                    $error = 'That username is already taken.';
                } else {
                    db()->prepare('INSERT INTO admins (username, password_hash, role, society_id, active, created_at) VALUES (?,?,?,?,1,NOW())')
                        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role, $role === 'super' ? null : $societyId]);
                    redirect(admin_url('admins') . '?added=1');
                }
            }
        } elseif ($action === 'toggle') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id !== (int) (current_admin()['id'] ?? 0)) {
                db()->prepare('UPDATE admins SET active = 1 - active WHERE id = ?')->execute([$id]);
            } else {
                $error = 'You cannot deactivate your own account.';
            }
            if (!$error) redirect(admin_url('admins') . '?saved=1');
        } elseif ($action === 'reset_password') {
            $id = (int) ($_POST['id'] ?? 0);
            $password = (string) ($_POST['password'] ?? '');
            if (strlen($password) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
                redirect(admin_url('admins') . '?saved=1');
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id === (int) (current_admin()['id'] ?? 0)) {
                $error = 'You cannot delete your own account.';
            } else {
                $st = db()->prepare("SELECT COUNT(*) FROM admins WHERE role = 'super' AND active = 1");
                $target = db()->prepare('SELECT role FROM admins WHERE id = ?');
                $target->execute([$id]);
                $targetRole = $target->fetchColumn();
                $st->execute();
                if ($targetRole === 'super' && (int) $st->fetchColumn() <= 1) {
                    $error = 'You cannot delete the last super admin.';
                } else {
                    db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
                    redirect(admin_url('admins') . '?deleted=1');
                }
            }
        }
    }
}

if (isset($_GET['added'])) $success = 'Admin account created.';
if (isset($_GET['saved'])) $success = 'Saved.';
if (isset($_GET['deleted'])) $success = 'Admin account deleted.';

$admins = db()->query(
    "SELECT a.*, s.name AS society_name FROM admins a LEFT JOIN societies s ON s.id = a.society_id ORDER BY a.role DESC, a.username"
)->fetchAll();
$societies = db()->query('SELECT id, name FROM societies ORDER BY name')->fetchAll();

render_admin('admin_admins_view', [
    'title' => 'Admin accounts | Admin | ' . cfg('site', 'name'),
    'admins' => $admins, 'societies' => $societies, 'error' => $error, 'success' => $success,
]);

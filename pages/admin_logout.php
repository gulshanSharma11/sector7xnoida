<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    unset($_SESSION['admin']);
}
redirect(admin_url('login'));

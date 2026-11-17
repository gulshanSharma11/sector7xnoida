<?php
/**
 * Mobile OTP login using the Call Astro API:
 *   POST {api_base}/vastu-user-login   {login_type, mobile_no, country_code}   -> sends OTP
 *   POST {api_base}/vastu-verify-otp   {login_type, mobile_no, country_code, otp} -> status 1 = verified
 */
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (current_user()) redirect($next);

$cc     = (string) cfg('auth', 'country_code', '91');
$step   = 'mobile';
$error  = '';
$info   = '';
$mobile = (string) ($_SESSION['otp_pending']['mobile'] ?? '');
$method = (string) ($_SESSION['otp_pending']['method'] ?? 'whatsapp');
if ($mobile !== '' && ($_SESSION['otp_pending']['at'] ?? 0) > time() - 900) $step = 'otp'; else { unset($_SESSION['otp_pending']); $mobile = ''; }

function login_count(string $where, array $args, int $minutes): int
{
    $st = db()->prepare("SELECT COUNT(*) FROM login_log WHERE $where AND created_at > (NOW() - INTERVAL $minutes MINUTE)");
    $st->execute($args);
    return (int) $st->fetchColumn();
}
function login_log(string $mobile, string $action, bool $ok): void
{
    db()->prepare('INSERT INTO login_log (mobile, ip, action, ok, created_at) VALUES (?,?,?,?,NOW())')
        ->execute([$mobile, client_ip(), $action, $ok ? 1 : 0]);
}
function astro_api(string $endpoint, array $payload): array
{
    $url = rtrim((string) cfg('auth', 'api_base'), '/') . $endpoint;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => (int) cfg('auth', 'timeout', 15),
        CURLOPT_USERAGENT      => 'Sector7xNoida/1.0',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $json = is_string($body) ? json_decode($body, true) : null;
    if ($body === false || !is_array($json)) {
        error_log('[Sector7xNoida] login API failed: ' . ($err ?: "HTTP $code") . ' ' . $endpoint);
        return ['ok' => false, 'message' => 'Login service is not reachable right now. Please try again in a minute.'];
    }
    $msg = (string) ($json['message'] ?? '');
    if (isset($json['errors']) && is_array($json['errors'])) {
        $first = reset($json['errors']);
        $msg = is_array($first) ? (string) reset($first) : (string) $first;
    }
    return ['ok' => ($code === 200 && (int) ($json['status'] ?? 0) === 1), 'message' => $msg, 'json' => $json];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif ($action === 'send') {
        $m   = preg_replace('/\D/', '', (string) ($_POST['mobile'] ?? ''));
        $mth = ($_POST['method'] ?? '') === 'sms' ? 'sms' : 'whatsapp';
        if (!preg_match('/^[6-9][0-9]{9}$/', $m)) {
            $error = 'Please enter a valid 10-digit mobile number.';
        } elseif (login_count('mobile = ? AND action = ?', [$m, 'send'], 60) >= (int) cfg('auth', 'max_otp_per_mobile_per_hour', 5)
               || login_count('ip = ? AND action = ?', [client_ip(), 'send'], 60) >= (int) cfg('auth', 'max_otp_per_ip_per_hour', 20)) {
            $error = 'Too many OTP requests. Please try again after some time.';
        } else {
            $r = astro_api((string) cfg('auth', 'send_endpoint'), ['login_type' => $mth, 'mobile_no' => $m, 'country_code' => $cc]);
            login_log($m, 'send', $r['ok']);
            if ($r['ok']) {
                $_SESSION['otp_pending'] = ['mobile' => $m, 'method' => $mth, 'at' => time()];
                $mobile = $m; $method = $mth; $step = 'otp';
                $info = 'OTP sent to +' . $cc . ' ' . mask_mobile($m) . ' via ' . ($mth === 'sms' ? 'SMS' : 'WhatsApp') . '.';
            } else {
                $error = $r['message'] !== '' ? $r['message'] : 'Could not send OTP. Please try again.';
            }
        }
    } elseif ($action === 'verify' && $mobile !== '') {
        $otp = preg_replace('/\D/', '', (string) ($_POST['otp'] ?? ''));
        if (!preg_match('/^[0-9]{4}$/', $otp)) {
            $error = 'Please enter the 4-digit OTP.'; $step = 'otp';
        } elseif (login_count('mobile = ? AND action = ?', [$mobile, 'verify'], 30) >= (int) cfg('auth', 'max_verify_per_mobile_per_30min', 8)) {
            $error = 'Too many wrong attempts. Please request a new OTP after some time.'; $step = 'otp';
        } else {
            $r = astro_api((string) cfg('auth', 'verify_endpoint'), ['login_type' => $method, 'mobile_no' => $mobile, 'country_code' => $cc, 'otp' => $otp]);
            login_log($mobile, 'verify', $r['ok']);
            if ($r['ok']) {
                $st = db()->prepare('INSERT INTO users (country_code, mobile, created_at, last_login_at) VALUES (?,?,NOW(),NOW())
                                     ON DUPLICATE KEY UPDATE last_login_at = NOW(), id = LAST_INSERT_ID(id)');
                $st->execute([$cc, $mobile]);
                $uid = (int) db()->lastInsertId();
                session_regenerate_id(true);
                unset($_SESSION['otp_pending']);
                $_SESSION['user'] = ['id' => $uid, 'mobile' => $mobile, 'country_code' => $cc];
                redirect($next);
            }
            $error = $r['message'] !== '' ? $r['message'] : 'Invalid or expired OTP.'; $step = 'otp';
        }
    } elseif ($action === 'change') {
        unset($_SESSION['otp_pending']); $mobile = ''; $step = 'mobile';
    }
}

render('login_view', [
    'title' => 'Login | ' . cfg('site', 'name'), 'desc' => 'Login to ' . cfg('site', 'name') . ' with your mobile number.',
    'robots' => 'noindex,nofollow',
    'step' => $step, 'error' => $error, 'info' => $info, 'mobile' => $mobile, 'method' => $method, 'next' => $next, 'cc' => $cc,
]);

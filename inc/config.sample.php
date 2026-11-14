<?php
/**
 * Sector7xNoida - settings TEMPLATE.
 *
 * Ye file git me commit hoti hai (sample ke taur par) - isme koi real password NAHI hona chahiye.
 * Server (ya local XAMPP) par iski copy "config.php" naam se banao aur usme real values bharo.
 * "config.php" khud git me kabhi commit nahi hoti (.gitignore me hai) - isliye deploy/pull se wo
 * kabhi overwrite nahi hogi, aur tumhare asli DB password/admin password kabhi GitHub par nahi jaayenge.
 *
 *   cp inc/config.sample.php inc/config.php
 */
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'CPANELUSER_sector7',   // cPanel prefix ke saath poora DB naam
        'user'    => 'CPANELUSER_s7user',    // cPanel prefix ke saath poora DB user
        'pass'    => 'CHANGE_THIS_PASSWORD',
        'charset' => 'utf8mb4',
    ],

    'site' => [
        'name'     => 'Sector7xNoida',
        'url'      => 'https://sector7xnoida.com',   // bina last slash ke
        'tagline'  => 'Trusted local numbers for your society',
        // Optional: "Number galat hai / naya add karna hai" ke liye WhatsApp number (91 ke saath, bina +). Khali = hide.
        'contact_whatsapp' => '',
    ],

    // Call Astro ka OTP login (API: vastu-user-login + vastu-verify-otp)
    'auth' => [
        'api_base'        => 'https://call-astro.com/api',
        'send_endpoint'   => '/vastu-user-login',
        'verify_endpoint' => '/vastu-verify-otp',
        'country_code'    => '91',
        'timeout'         => 15,
        // true = phone numbers sirf login ke baad dikhenge. false = sab ko dikhenge (login optional).
        'require_login_for_contact' => false,
        // Spam rokne ke liye limits
        'max_otp_per_mobile_per_hour' => 5,
        'max_otp_per_ip_per_hour'     => 20,
        'max_verify_per_mobile_per_30min' => 8,
    ],

    // Admin panel (/admin) - societies/categories/providers/reviews manage karne ke liye.
    // Yahan ek strong password daal do aur kisi ko mat batao.
    'admin' => [
        'password' => 'CHANGE_THIS_ADMIN_PASSWORD',
    ],

    'debug' => false,   // true sirf testing ke time (errors screen pe dikhte hain)
];

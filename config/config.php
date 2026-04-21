<?php
/**
 * config/config.php
 * Central configuration — edit this file before first launch.
 */

// ─── Environment ──────────────────────────────────────────────────────────────
define('APP_ENV',  'development');   // 'development' | 'production'
define('APP_NAME', 'AuthSystem');
define('APP_URL',  'http://localhost/auth_system'); // no trailing slash

// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'auth_system');
define('DB_USER',    'root');       // ← change in production
define('DB_PASS',    '');           // ← change in production
define('DB_CHARSET', 'utf8mb4');

// ─── Session ──────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 7200);   // seconds (2 hours)
define('SESSION_NAME',     'AUTH_SID');

// ─── Security ─────────────────────────────────────────────────────────────────
define('HASH_ALGO',        PASSWORD_BCRYPT);
define('HASH_COST',        12);
define('OTP_LENGTH',       6);
define('OTP_EXPIRY_MINS',  15);     // OTP valid for 15 minutes
define('MAX_LOGIN_ATTEMPTS', 5);    // lock after N failures
define('LOCKOUT_MINUTES',  15);     // lockout window

// ─── Mailer (PHP mail() by default — swap for SMTP in includes/Mailer.php) ───
define('MAIL_FROM_NAME',  APP_NAME . ' Security');
define('MAIL_FROM_ADDR',  'no-reply@yourdomain.com'); // ← change this

// ─── Paths ────────────────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('PAGES_PATH', BASE_PATH . '/pages');

// ─── Error display ────────────────────────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
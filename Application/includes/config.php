<?php
/**
 * Re-fill — Application Configuration
 *
 * I centralise all environment-specific settings here so I only need
 * to change one file when moving between local dev (XAMPP) and production.
 * Every page loads this indirectly through db.php -> config.php.
 */

// ── Base URL ─────────────────────────────────────────────────
// I use a constant rather than a relative path because the app lives in a
// subdirectory (/refill/) on XAMPP. On a production root domain, set this to ''.
define('BASE_URL', '/refill');

// ── App settings ─────────────────────────────────────────────
define('APP_NAME',    'Re-fill');
define('APP_VERSION', '1.0.0');
// APP_ENV controls error display — I never want stack traces visible in production
define('APP_ENV',     'development'); // Change to 'production' before deploying

// ── Error reporting ──────────────────────────────────────────
// I suppress all output in production so PHP errors don't leak internal paths
// or stack traces to users (a common information disclosure vulnerability).
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

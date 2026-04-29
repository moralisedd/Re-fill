<?php
/**
 * Re-fill — Application Configuration
 *
 * I centralise all environment-specific settings here so I only need
 * to change one file when moving between local dev (XAMPP) and production.
 * Every page loads this indirectly through db.php -> config.php.
 */

// Lock PHP to UTC so date() always matches MySQL's NOW().
// Without this, a BST/local-timezone server produces timestamps hours ahead of
// MySQL's UTC clock, causing tokens to outlive their intended expiry.
date_default_timezone_set('UTC');

// BASE_URL is a constant rather than a relative path because the app lives in a
// subdirectory (/refill/) on XAMPP. Set to '' on a production root domain.
define('BASE_URL', '/refill');

define('APP_NAME',    'Re-fill');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // Change to 'production' before deploying

// REDEMPTION_SECRET signs reward QR payloads with HMAC-SHA256.
// Staff-side validation re-computes the HMAC, so a tampered payload is rejected
// before any DB work. Move this to an environment variable in production.
define('REDEMPTION_SECRET', 'rfill_redm_k3y_changeme_in_prod_a9b2c7d4e1f8');

// Suppress error output in production to prevent path/stack-trace leaks.
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

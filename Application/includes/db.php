<?php
/**
 * Re-fill — Database Connection
 *
 * PDO with EMULATE_PREPARES=false forces real server-side prepared statements.
 * Client-side emulation still constructs the query string locally and can be
 * vulnerable to multi-byte encoding attacks that true preparation avoids.
 */

require_once __DIR__ . '/config.php';

define('DB_HOST',    'localhost');
define('DB_NAME',    'refill');
define('DB_USER',    'root');  // Update for your XAMPP/MySQL user
define('DB_PASS',    '');      // Update for your MySQL password
define('DB_CHARSET', 'utf8mb4'); // utf8mb4 supports full Unicode including emoji

/**
 * Returns a shared PDO connection (singleton).
 * Static $pdo means one connection per request rather than one per call.
 */
function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Lock the MySQL session timezone to UTC so NOW() matches PHP's date()
            // output. Shared hosts (e.g. InfinityFree) can have PHP and MySQL on
            // different offsets, which makes expires_at > NOW() comparisons unreliable.
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real error, never expose it to the browser (leaks DB host, name, credentials)
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database unavailable. Please try again later.']));
        }
    }

    return $pdo;
}

<?php
/**
 * Re-fill — Database Connection
 *
 * I use PDO with prepared statements throughout because raw mysqli queries
 * are a SQL injection risk. EMULATE_PREPARES = false forces real prepared
 * statements at the MySQL level, not just client-side string substitution.
 */

require_once __DIR__ . '/config.php';

define('DB_HOST',    'localhost');
define('DB_NAME',    'refill');
define('DB_USER',    'root');  // Update for your XAMPP/MySQL user
define('DB_PASS',    '');      // Update for your MySQL password
define('DB_CHARSET', 'utf8mb4'); // utf8mb4 supports full Unicode including emoji

/**
 * Returns a shared PDO connection (singleton pattern).
 * I use static $pdo so I only open one connection per request — opening
 * a new connection on every function call would be unnecessarily expensive.
 */
function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw on error so I can catch it
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Always return named keys
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Real prepared statements only
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // I log the real error but never send it to the browser — it would
            // expose the DB name, host, and credentials to anyone watching.
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database unavailable. Please try again later.']));
        }
    }

    return $pdo;
}

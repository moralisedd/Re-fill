<?php
/**
 * Re-fill — Authentication Helpers
 *
 * I handle all session management and access control here so there's one
 * place to audit security decisions. Both customer and staff flows run through
 * this file. Sessions are regenerated on every login to prevent session fixation —
 * where an attacker pre-sets a known session ID and waits for the victim to authenticate.
 */

require_once __DIR__ . '/db.php';

// ── Session hardening ────────────────────────────────────────────────────────
// I set these before session_start() so they apply to every page load.
// httponly blocks JavaScript from reading the session cookie (XSS mitigation).
// strict_mode rejects any session ID not created by the server — prevents fixation.
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '0');    // Flip to '1' when running on HTTPS
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime',  '3600'); // 1-hour idle timeout

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Customer authentication ──────────────────────────────────────────────────

/**
 * Attempt to log in a customer by email and password.
 * I regenerate the session ID on success to prevent session fixation attacks.
 * Returns the user row (minus the password hash) on success, false on failure.
 */
function customer_login(string $email, string $password): array|false {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true); // New session ID post-login — prevents fixation
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = 'customer';
        unset($user['password_hash']); // I never keep the hash in session memory
        return $user;
    }

    return false;
}

/**
 * Attempt to log in a cafe staff member.
 * I JOIN cafes here so the staff session carries the cafe name — avoids
 * an extra query on every page that needs to display the cafe name.
 */
function staff_login(string $email, string $password): array|false {
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        'SELECT s.*, c.name AS cafe_name
         FROM cafe_staff s
         JOIN cafes c ON s.cafe_id = c.cafe_id
         WHERE s.email = ? AND s.is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['staff_id']   = $staff['staff_id'];
        $_SESSION['cafe_id']    = $staff['cafe_id'];
        $_SESSION['staff_name'] = $staff['full_name'];
        $_SESSION['staff_role'] = $staff['role'];    // 'owner' or 'barista'
        $_SESSION['role']       = 'staff';
        unset($staff['password_hash']);
        return $staff;
    }

    return false;
}

/**
 * Destroy the session and expire the cookie.
 * I manually expire the cookie as well as calling session_destroy() because
 * session_destroy() alone doesn't remove the cookie from the client's browser.
 */
function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ── Access guards ────────────────────────────────────────────────────────────
// I call these at the top of every protected page — they redirect and exit
// immediately so no page content ever renders for unauthenticated users.

function require_customer(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/customer/login.php');
        exit;
    }
}

function require_staff(): void {
    if (empty($_SESSION['staff_id'])) {
        header('Location: ' . BASE_URL . '/staff/login.php');
        exit;
    }
}

/**
 * Owner-only guard — used by the admin panel.
 * I check staff auth first, then role. A barista who somehow hit this URL
 * gets a 403, not a redirect, so they know access was denied not just lost.
 */
function require_owner(): void {
    require_staff();
    if ($_SESSION['staff_role'] !== 'owner') {
        http_response_code(403);
        die('Access denied — owner role required.');
    }
}

function is_logged_in_customer(): bool {
    return !empty($_SESSION['user_id']);
}

function is_logged_in_staff(): bool {
    return !empty($_SESSION['staff_id']);
}

// ── Password utilities ───────────────────────────────────────────────────────

/**
 * Hash a password with bcrypt at cost 12.
 * Cost 12 is the OWASP-recommended minimum for bcrypt — slow enough to resist
 * brute force but fast enough not to noticeably impact login performance.
 */
function hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Enforce a minimum password policy: ≥8 chars with upper, lower, digit, and special.
 * I use a regex here rather than multiple strlen/preg_match calls — one pass is cleaner.
 */
function is_strong_password(string $plain): bool {
    return preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        $plain
    ) === 1;
}

<?php
/**
 * Re-fill — QR Token Engine
 *
 * I modelled this on Alipay's Customer-Presented Mode (CPM):
 *  - The customer generates a token on their own device
 *  - Staff scan or manually enter the code to validate the transaction
 *  - Tokens expire after 60 seconds to prevent screenshot abuse
 *  - Each token has a single-use nonce — so even if someone screenshots
 *    a valid token, it can't be replayed after one use
 *
 * I use random_bytes() for all token/nonce generation because it draws from
 * the OS CSPRNG. rand() and mt_rand() are NOT cryptographically secure.
 */

require_once __DIR__ . '/db.php';

define('QR_TOKEN_TTL_SECONDS', 60);
define('POINTS_PER_VISIT',     1);

/**
 * Generate a fresh QR token for the authenticated customer.
 * I invalidate any existing unexpired tokens first so there's only ever
 * one active token per user — prevents a customer holding multiple valid codes.
 *
 * @return array ['token_value' => string, 'expires_at' => string, 'nonce' => string]
 */
function generate_qr_token(int $user_id): array {
    $pdo = get_db();

    // Expire any previous active tokens — one active code per user at a time
    $pdo->prepare(
        'UPDATE qr_tokens SET is_used = 1
         WHERE user_id = ? AND is_used = 0 AND expires_at > NOW()'
    )->execute([$user_id]);

    // 64-char token + 32-char nonce — both from the OS CSPRNG via random_bytes()
    $token_value = bin2hex(random_bytes(32));
    $nonce       = bin2hex(random_bytes(16));

    // I compute expires_at with gmdate() which always outputs UTC regardless of the
    // server's local clock — it is the PHP equivalent of MySQL's UTC_TIMESTAMP().
    //
    // A previous attempt used NOW() + INTERVAL ? SECOND directly in the INSERT, but
    // MySQL's binary prepared-statement protocol (required when EMULATE_PREPARES=false)
    // does not support INTERVAL with a bound placeholder. The parameter silently binds
    // as NULL, the INSERT stores an invalid expiry, and LAST_INSERT_ID() then returns
    // a stale ID from an earlier session — pulling an old token row that is already
    // expired, which is why the QR page showed "Expired" immediately on load.
    //
    // Timezone consistency is guaranteed by two other layers:
    //   1. config.php — date_default_timezone_set('UTC'): PHP date functions → UTC
    //   2. db.php     — SET time_zone = '+00:00':         MySQL NOW()        → UTC
    // Both sides of the expires_at > NOW() comparison in validate_qr_token() now
    // operate on the same UTC clock.
    $expires_at = gmdate('Y-m-d H:i:s', time() + QR_TOKEN_TTL_SECONDS);

    $stmt = $pdo->prepare(
        'INSERT INTO qr_tokens (user_id, token_value, nonce, expires_at)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$user_id, $token_value, $nonce, $expires_at]);

    return [
        'token_value' => $token_value,
        'nonce'       => $nonce,
        'expires_at'  => $expires_at,
        'expires_in'  => QR_TOKEN_TTL_SECONDS,
    ];
}

/**
 * Validate a QR token scanned or entered by cafe staff.
 * I run this inside a transaction with a row-level lock (FOR UPDATE) to prevent
 * a race condition where two staff members scan the same code simultaneously —
 * without the lock, both reads could see is_used = 0 before either write completes.
 *
 * Returns ['success' => true, ...customer data] on success,
 * or ['success' => false, 'error' => string] on failure.
 */
function validate_qr_token(string $token_value, string $nonce, int $cafe_id, int $staff_id): array {
    $pdo = get_db();

    // FOR UPDATE locks the row so no other connection can read-and-update simultaneously
    $stmt = $pdo->prepare(
        'SELECT * FROM qr_tokens
         WHERE token_value = ?
           AND nonce       = ?
           AND is_used     = 0
           AND expires_at  > NOW()
         LIMIT 1
         FOR UPDATE'
    );

    $pdo->beginTransaction();

    try {
        $stmt->execute([$token_value, $nonce]);
        $token = $stmt->fetch();

        if (!$token) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Token is invalid, expired, or already used.'];
        }

        $user_id  = (int) $token['user_id'];
        $token_id = (int) $token['token_id'];

        // Mark token as used
        $pdo->prepare('UPDATE qr_tokens SET is_used = 1 WHERE token_id = ?')
            ->execute([$token_id]);

        // Award points to the customer
        $pdo->prepare(
            'UPDATE users SET points_balance = points_balance + ? WHERE user_id = ?'
        )->execute([POINTS_PER_VISIT, $user_id]);

        // Record the transaction
        $pdo->prepare(
            'INSERT INTO transactions (user_id, cafe_id, staff_id, token_id, transaction_type, points_delta)
             VALUES (?, ?, ?, ?, "earn", ?)'
        )->execute([$user_id, $cafe_id, $staff_id, $token_id, POINTS_PER_VISIT]);

        // Fetch updated balance
        $bal = $pdo->prepare('SELECT points_balance, full_name FROM users WHERE user_id = ?');
        $bal->execute([$user_id]);
        $user = $bal->fetch();

        $pdo->commit();

        return [
            'success'        => true,
            'customer_name'  => $user['full_name'],
            'points_awarded' => POINTS_PER_VISIT,
            'points_balance' => (int) $user['points_balance'],
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('QR validation error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'An error occurred. Please try again.'];
    }
}

/**
 * Redeem a reward for a customer.
 * Deducts points and logs the redemption transaction.
 */
function redeem_reward(int $user_id, int $reward_id, int $cafe_id, int $staff_id): array {
    $pdo = get_db();

    $pdo->beginTransaction();

    try {
        // Fetch reward
        $stmt = $pdo->prepare('SELECT * FROM rewards WHERE reward_id = ? AND is_active = 1 FOR UPDATE');
        $stmt->execute([$reward_id]);
        $reward = $stmt->fetch();

        if (!$reward) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Reward not found or unavailable.'];
        }

        // Fetch user balance
        $uStmt = $pdo->prepare('SELECT points_balance, full_name FROM users WHERE user_id = ? FOR UPDATE');
        $uStmt->execute([$user_id]);
        $user = $uStmt->fetch();

        if (!$user || $user['points_balance'] < $reward['points_required']) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Insufficient points.'];
        }

        // Deduct points
        $pdo->prepare(
            'UPDATE users SET points_balance = points_balance - ? WHERE user_id = ?'
        )->execute([$reward['points_required'], $user_id]);

        // Record transaction
        $pdo->prepare(
            'INSERT INTO transactions (user_id, cafe_id, staff_id, reward_id, transaction_type, points_delta)
             VALUES (?, ?, ?, ?, "redeem", ?)'
        )->execute([$user_id, $cafe_id, $staff_id, $reward_id, -$reward['points_required']]);

        $pdo->commit();

        return [
            'success'          => true,
            'reward_name'      => $reward['name'],
            'points_used'      => $reward['points_required'],
            'new_balance'      => $user['points_balance'] - $reward['points_required'],
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Redemption error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'An error occurred. Please try again.'];
    }
}

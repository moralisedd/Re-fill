<?php
/**
 * Re-fill — Reward Redemption API
 *
 * POST /api/redeem_qr.php
 * Body (JSON): { "user_id": int, "reward_id": int, "expires": int, "sig": string }
 *
 * The HMAC-SHA256 signature is verified before any database work. rewards.php
 * generates it server-side using REDEMPTION_SECRET, so a tampered payload
 * (wrong user, wrong reward, wrong expiry) fails hash_equals() immediately.
 * hash_equals() is constant-time to prevent timing oracle attacks.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/qr.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

if (!is_logged_in_staff()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

try {
    $body = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (\JsonException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$user_id   = isset($body['user_id'])   ? (int)$body['user_id']   : 0;
$reward_id = isset($body['reward_id']) ? (int)$body['reward_id'] : 0;
$expires   = isset($body['expires'])   ? (int)$body['expires']   : 0;
$sig       = trim($body['sig'] ?? '');

if (!$user_id || !$reward_id || !$expires || empty($sig)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

// Expiry check first: fail fast on obviously stale codes before touching the DB
if (time() > $expires) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Redemption code has expired. Ask the customer to refresh their rewards page.']);
    exit;
}

// Re-compute expected HMAC using the same inputs rewards.php signed with
$expected = hash_hmac(
    'sha256',
    $user_id . '|' . $reward_id . '|' . $expires,
    REDEMPTION_SECRET
);

if (!hash_equals($expected, $sig)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid redemption code.']);
    exit;
}

$cafe_id  = (int)$_SESSION['cafe_id'];
$staff_id = (int)$_SESSION['staff_id'];

$result = redeem_reward($user_id, $reward_id, $cafe_id, $staff_id);

http_response_code($result['success'] ? 200 : 422);
echo json_encode($result);

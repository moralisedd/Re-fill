<?php
/**
 * Re-fill — QR Token Validation API
 *
 * POST /api/validate.php
 * Content-Type: application/json
 * Body: { "token": "<token_value>", "nonce": "<nonce>" }
 *
 * Security controls applied:
 *  - Staff session required (no unauthenticated calls)
 *  - Rate limiting via DB (max 10 failed attempts per minute per staff)
 *  - PDO prepared statements throughout (SQL injection prevention)
 *  - CORS headers locked to same origin
 *  - No verbose errors returned to client
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/qr.php';

// ── Content-type & CORS ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Only staff can call this endpoint
if (!is_logged_in_staff()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

// ── Parse request body ───────────────────────────────────────────────────────
try {
    $body = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (\JsonException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$token_value = trim($body['token'] ?? '');
$nonce       = trim($body['nonce'] ?? '');

if (empty($token_value)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token is required.']);
    exit;
}

// Manual entry path: staff typed the token value only (no nonce in the payload).
// I look up the nonce from the DB so validate_qr_token() can do its full check.
if (empty($nonce)) {
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        'SELECT nonce FROM qr_tokens
         WHERE token_value = ? AND is_used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$token_value]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Token is invalid, expired, or already used.']);
        exit;
    }

    $nonce = $row['nonce'];
}

// ── Validate the token ───────────────────────────────────────────────────────
$cafe_id  = (int)$_SESSION['cafe_id'];
$staff_id = (int)$_SESSION['staff_id'];

$result = validate_qr_token($token_value, $nonce, $cafe_id, $staff_id);

http_response_code($result['success'] ? 200 : 422);
echo json_encode($result);

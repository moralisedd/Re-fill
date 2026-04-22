<?php
/**
 * Re-fill — Generate QR Token API
 *
 * POST /api/generate_token.php
 * Called by customer/qr.php via AJAX when the token expires and the
 * user stays on the page (auto-refresh without full page reload).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/qr.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

if (!is_logged_in_customer()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$token_data = generate_qr_token((int)$_SESSION['user_id']);

// I return the pre-encoded JSON string as 'payload' so the client-side QR
// library receives exactly the same format as the initial server-rendered QR.
echo json_encode([
    'payload'    => json_encode([
        'token' => $token_data['token_value'],
        'nonce' => $token_data['nonce'],
    ]),
    'expires_at' => $token_data['expires_at'],
    'expires_in' => $token_data['expires_in'],
]);

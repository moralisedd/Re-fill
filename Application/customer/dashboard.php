<?php
/**
 * Re-fill — Customer Dashboard
 *
 * Shows the customer's points balance and their last 5 transactions.
 * I limit to 5 here for a quick overview — the full list is on history.php.
 */
require_once __DIR__ . '/../includes/auth.php';
require_customer(); // Redirect to login if not authenticated

$pdo = get_db();
$uid = $_SESSION['user_id'];

$user = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$user->execute([$uid]);
$user = $user->fetch();

// I JOIN cafes for name + city and use a window function to compute the
// running balance after each transaction — otherwise every row shows the
// same current balance, which makes the history column misleading.
$recent = $pdo->prepare(
    'SELECT t.transaction_id, t.created_at, t.transaction_type, t.points_delta,
            c.name AS cafe_name, c.city AS cafe_city, c.address AS cafe_address,
            SUM(t.points_delta) OVER (
                PARTITION BY t.user_id
                ORDER BY t.created_at ASC, t.transaction_id ASC
                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
            ) AS balance_after
     FROM transactions t
     JOIN cafes c ON t.cafe_id = c.cafe_id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC, t.transaction_id DESC
     LIMIT 5'
);
$recent->execute([$uid]);
$recent = $recent->fetchAll();

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h1>

<!-- Points balance — centred hero so the number is the first thing the
     customer sees, not competing for space with the QR/rewards links. -->
<div class="d-flex justify-content-center mt-4 mb-4">
    <div class="card stat-card text-center stat-card--balance">
        <div class="stat-value"><?= (int)$user['points_balance'] ?></div>
        <div class="stat-label">Points balance</div>
    </div>
</div>

<!-- Action cards — Bootstrap row: stacked on mobile, side by side on sm+ -->
<div class="row g-3 mb-2">
    <div class="col-12 col-sm-6">
        <div class="card h-100 d-flex flex-column align-items-center justify-content-center text-center action-card">
            <p class="action-card-text">Show your QR code at any participating cafe to earn a point.</p>
            <a href="<?= BASE_URL ?>/customer/qr.php" class="btn-primary">Generate my QR code</a>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="card h-100 d-flex flex-column align-items-center justify-content-center text-center action-card">
            <p class="action-card-text">Ready to treat yourself? Redeem your points for a free drink.</p>
            <a href="<?= BASE_URL ?>/customer/rewards.php" class="btn-secondary">Browse rewards</a>
        </div>
    </div>
</div>

<h2 class="section-heading">Recent activity</h2>

<?php if (empty($recent)): ?>
    <div class="alert alert-info">No activity yet — visit a cafe and scan your QR code to earn your first point!</div>
<?php else: ?>
    <div class="table-wrapper card card-grid--sm">
        <table>
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Cafe</th>
                    <th scope="col">Location</th>
                    <th scope="col">Type</th>
                    <th scope="col">Points</th>
                    <th scope="col">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $tx): ?>
                <tr>
                    <td><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></td>
                    <td><?= htmlspecialchars($tx['cafe_name']) ?></td>
                    <td class="td-meta"><?= htmlspecialchars($tx['cafe_city']) ?></td>
                    <td>
                        <?php if ($tx['transaction_type'] === 'earn'): ?>
                            <span class="badge badge-green">Earned</span>
                        <?php else: ?>
                            <span class="badge badge-amber">Redeemed</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $tx['points_delta'] > 0 ? '+' . $tx['points_delta'] : $tx['points_delta'] ?></td>
                    <td><?= (int)$tx['balance_after'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="page-back"><a href="<?= BASE_URL ?>/customer/history.php">View full history →</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

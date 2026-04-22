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

// I JOIN cafes so I can show the cafe name without a second query per row
$recent = $pdo->prepare(
    'SELECT t.*, c.name AS cafe_name
     FROM transactions t
     JOIN cafes c ON t.cafe_id = c.cafe_id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC
     LIMIT 5'
);
$recent->execute([$uid]);
$recent = $recent->fetchAll();

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h1>

<div class="card-grid" style="margin-top:1.5rem;">
    <div class="card stat-card">
        <div class="stat-value"><?= (int)$user['points_balance'] ?></div>
        <div class="stat-label">Points balance</div>
    </div>
    <div class="card" style="display:flex; align-items:center; justify-content:center; flex-direction:column; gap:.75rem; text-align:center; padding:1.75rem;">
        <p style="margin:0; font-size:.95rem;">Show your QR code at any participating cafe to earn a point.</p>
        <a href="<?= BASE_URL ?>/customer/qr.php" class="btn-primary">Generate my QR code</a>
    </div>
    <div class="card" style="display:flex; align-items:center; justify-content:center; flex-direction:column; gap:.75rem; text-align:center; padding:1.75rem;">
        <p style="margin:0; font-size:.95rem;">Ready to treat yourself? Redeem your points for a free drink.</p>
        <a href="<?= BASE_URL ?>/customer/rewards.php" class="btn-secondary">Browse rewards</a>
    </div>
</div>

<h2 style="margin-top:2rem;">Recent activity</h2>

<?php if (empty($recent)): ?>
    <div class="alert alert-info">No activity yet — visit a cafe and scan your QR code to earn your first point!</div>
<?php else: ?>
    <div class="table-wrapper card" style="padding:0; margin-top:1rem;">
        <table>
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Cafe</th>
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
                    <td>
                        <?php if ($tx['transaction_type'] === 'earn'): ?>
                            <span class="badge badge-green">Earned</span>
                        <?php else: ?>
                            <span class="badge badge-amber">Redeemed</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $tx['points_delta'] > 0 ? '+' . $tx['points_delta'] : $tx['points_delta'] ?></td>
                    <td><?= (int)$user['points_balance'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top:.75rem;"><a href="<?= BASE_URL ?>/customer/history.php">View full history →</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

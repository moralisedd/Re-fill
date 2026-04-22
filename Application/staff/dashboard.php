<?php
/**
 * Re-fill — Staff Dashboard
 *
 * Shows today's scan stats and the 10 most recent transactions for this cafe.
 * I scope all queries to cafe_id from the session — a barista at one cafe
 * should never see transaction data from another cafe.
 */
require_once __DIR__ . '/../includes/auth.php';
require_staff();

$pdo     = get_db();
$cafe_id = (int)$_SESSION['cafe_id'];

// Today's totals — I use CURDATE() so MySQL handles the timezone, not PHP
$today_stmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS total_scans,
        SUM(CASE WHEN transaction_type = "earn" THEN 1 ELSE 0 END) AS points_awarded,
        COUNT(DISTINCT user_id) AS unique_customers
     FROM transactions
     WHERE cafe_id = ? AND DATE(created_at) = CURDATE()'
);
$today_stmt->execute([$cafe_id]);
$stats = $today_stmt->fetch();

// Last 10 transactions — JOIN users to get the customer name for the table
$recent = $pdo->prepare(
    'SELECT t.*, u.full_name AS customer_name
     FROM transactions t
     JOIN users u ON t.user_id = u.user_id
     WHERE t.cafe_id = ?
     ORDER BY t.created_at DESC
     LIMIT 10'
);
$recent->execute([$cafe_id]);
$recent = $recent->fetchAll();

$page_title = 'Staff Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1><?= htmlspecialchars($_SESSION['cafe_name'] ?? 'Cafe') ?></h1>
        <p style="color:var(--colour-text-muted);">Welcome, <?= htmlspecialchars($_SESSION['staff_name']) ?> &bull; <?= ucfirst($_SESSION['staff_role']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/staff/scan.php" class="btn-primary" style="font-size:1.1rem; padding:.75rem 1.5rem;">📷 Scan QR Code</a>
</div>

<div class="card-grid" style="margin-top:1.5rem;">
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['total_scans'] ?? 0) ?></div>
        <div class="stat-label">Scans today</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['points_awarded'] ?? 0) ?></div>
        <div class="stat-label">Points awarded today</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['unique_customers'] ?? 0) ?></div>
        <div class="stat-label">Unique customers today</div>
    </div>
</div>

<h2 style="margin-top:2rem;">Recent transactions</h2>

<?php if (empty($recent)): ?>
    <div class="alert alert-info">No transactions yet today.</div>
<?php else: ?>
    <div class="table-wrapper card" style="padding:0; margin-top:1rem;">
        <table>
            <thead>
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Type</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $tx): ?>
                <tr>
                    <td><?= date('H:i', strtotime($tx['created_at'])) ?></td>
                    <td><?= htmlspecialchars($tx['customer_name']) ?></td>
                    <td>
                        <?php if ($tx['transaction_type'] === 'earn'): ?>
                            <span class="badge badge-green">Earn</span>
                        <?php else: ?>
                            <span class="badge badge-amber">Redeem</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $tx['points_delta'] > 0 ? '+' . $tx['points_delta'] : $tx['points_delta'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem;">
    <a href="<?= BASE_URL ?>/staff/logout.php">Log out</a>
    <?php if ($_SESSION['staff_role'] === 'owner'): ?>
     &bull; <a href="<?= BASE_URL ?>/admin/index.php">Admin panel →</a>
    <?php endif; ?>
</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

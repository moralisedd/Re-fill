<?php
/**
 * Re-fill — Admin / Owner Panel
 *
 * I restrict this to the 'owner' role via require_owner() — a barista who
 * tries to navigate here directly gets a 403, not a redirect. The owner sees
 * all-time cafe analytics and can view the rewards catalogue. I kept this
 * read-only for now — reward editing would need a CSRF-protected form.
 */
require_once __DIR__ . '/../includes/auth.php';
require_owner();

$pdo     = get_db();
$cafe_id = (int)$_SESSION['cafe_id'];

// All-time totals for this cafe only — scoped by cafe_id so multi-cafe data stays separate
$stats = $pdo->prepare(
    'SELECT
        COUNT(DISTINCT user_id)                                          AS total_customers,
        COUNT(*)                                                         AS total_transactions,
        SUM(CASE WHEN transaction_type = "earn"   THEN points_delta ELSE 0 END) AS total_awarded,
        SUM(CASE WHEN transaction_type = "redeem" THEN ABS(points_delta) ELSE 0 END) AS total_redeemed
     FROM transactions WHERE cafe_id = ?'
);
$stats->execute([$cafe_id]);
$stats = $stats->fetch();

// All rewards
$rewards = $pdo->query('SELECT * FROM rewards ORDER BY points_required ASC')->fetchAll();

$page_title = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Admin Panel — <?= htmlspecialchars($_SESSION['cafe_name'] ?? '') ?></h1>

<div class="card-grid card-grid--md">
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['total_customers'] ?? 0) ?></div>
        <div class="stat-label">Total customers</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['total_transactions'] ?? 0) ?></div>
        <div class="stat-label">Total transactions</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['total_awarded'] ?? 0) ?></div>
        <div class="stat-label">Points awarded</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= (int)($stats['total_redeemed'] ?? 0) ?></div>
        <div class="stat-label">Points redeemed</div>
    </div>
</div>

<h2 class="section-heading">Rewards catalogue</h2>

<div class="table-wrapper card card-grid--sm">
    <table>
        <thead>
            <tr>
                <th scope="col">Reward</th>
                <th scope="col">Points required</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rewards as $r): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($r['name']) ?></strong><br>
                    <span class="reward-desc-sm"><?= htmlspecialchars($r['description'] ?? '') ?></span>
                </td>
                <td><?= (int)$r['points_required'] ?></td>
                <td>
                    <span class="badge <?= $r['is_active'] ? 'badge-green' : 'badge-red' ?>">
                        <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="page-back">
    <a href="<?= BASE_URL ?>/staff/dashboard.php">← Back to staff dashboard</a>
</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

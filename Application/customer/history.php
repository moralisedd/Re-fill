<?php
/**
 * Re-fill — Transaction History
 *
 * Full transaction log for the authenticated customer, newest first.
 * This is the unbounded version of what dashboard.php shows — no LIMIT clause
 * because customers should be able to see their complete history.
 */
require_once __DIR__ . '/../includes/auth.php';
require_customer();

$pdo = get_db();
$uid = $_SESSION['user_id'];

// JOIN cafes so each row shows which cafe the transaction happened at
$stmt = $pdo->prepare(
    'SELECT t.*, c.name AS cafe_name
     FROM transactions t
     JOIN cafes c ON t.cafe_id = c.cafe_id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC'
);
$stmt->execute([$uid]);
$history = $stmt->fetchAll();

$page_title = 'Transaction History';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Transaction history</h1>

<?php if (empty($history)): ?>
    <div class="alert alert-info">You haven't made any transactions yet.</div>
<?php else: ?>
    <div class="table-wrapper card" style="padding:0; margin-top:1.5rem;">
        <table>
            <thead>
                <tr>
                    <th scope="col">Date &amp; Time</th>
                    <th scope="col">Cafe</th>
                    <th scope="col">Type</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $tx): ?>
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
                    <td style="font-weight:600; color:<?= $tx['points_delta'] > 0 ? 'var(--colour-success)' : 'var(--colour-error)' ?>">
                        <?= $tx['points_delta'] > 0 ? '+' . $tx['points_delta'] : $tx['points_delta'] ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

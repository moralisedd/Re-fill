<?php
/**
 * Re-fill — Rewards Catalogue
 *
 * I fetch the customer's current balance first so I can flag which rewards
 * they can already afford. The can_redeem flag is calculated in PHP rather
 * than SQL to keep the query simple and avoid a correlated subquery.
 */
require_once __DIR__ . '/../includes/auth.php';
require_customer();

$pdo = get_db();
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT points_balance FROM users WHERE user_id = ?');
$stmt->execute([$uid]);
$balance = (int)$stmt->fetchColumn();

// Only show active rewards — inactive ones are hidden by the owner
$rewards = $pdo->query('SELECT * FROM rewards WHERE is_active = 1 ORDER BY points_required ASC')->fetchAll();

$page_title = 'Rewards';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Rewards</h1>
<p>You have <strong><?= $balance ?> point<?= $balance !== 1 ? 's' : '' ?></strong> to spend.</p>

<?php if (empty($rewards)): ?>
    <div class="alert alert-info">No rewards available right now. Check back soon!</div>
<?php else: ?>
    <div class="card-grid" style="margin-top:1.5rem;">
        <?php foreach ($rewards as $r): ?>
            <?php $can_redeem = $balance >= $r['points_required']; ?>
            <div class="card" style="display:flex; flex-direction:column; gap:.75rem;">
                <div>
                    <h2 style="font-size:1.1rem;"><?= htmlspecialchars($r['name']) ?></h2>
                    <p style="color:var(--colour-text-muted); font-size:.9rem; margin:0;">
                        <?= htmlspecialchars($r['description'] ?? '') ?>
                    </p>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                    <span class="badge <?= $can_redeem ? 'badge-green' : 'badge-red' ?>">
                        <?= (int)$r['points_required'] ?> point<?= $r['points_required'] !== 1 ? 's' : '' ?>
                    </span>
                    <?php if ($can_redeem): ?>
                        <span style="font-size:.8rem; color:var(--colour-text-muted);">Ask staff to redeem</span>
                    <?php else: ?>
                        <span style="font-size:.8rem; color:var(--colour-error);">
                            Need <?= (int)$r['points_required'] - $balance ?> more
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

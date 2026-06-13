<?php
$pageTitle = 'Activity History';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = getCurrentUser();

$stmt = $pdo->prepare(
    'SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 50'
);
$stmt->execute([$_SESSION['user_id']]);
$activities = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <h1><i class="fas fa-history"></i> Activity History</h1>

    <?php if (empty($activities)): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <h3>No activity recorded yet</h3>
        </div>
    <?php else: ?>
        <div class="activity-timeline">
            <?php foreach ($activities as $act): ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4><?= sanitize($act['action']) ?></h4>
                        <?php if ($act['details']): ?>
                            <p><?= sanitize($act['details']) ?></p>
                        <?php endif; ?>
                        <small><i class="fas fa-clock"></i> <?= date('M j, Y g:i A', strtotime($act['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

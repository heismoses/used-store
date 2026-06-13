<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $reportId = (int) ($_POST['report_id'] ?? 0);
    $status = $_POST['status'] ?? 'reviewed';
    $note = trim($_POST['admin_note'] ?? '');

    $pdo->prepare('UPDATE reports SET status = ?, admin_note = ? WHERE id = ?')
        ->execute([$status, sanitize($note), $reportId]);
    setFlash('success', 'Report updated.');
    redirect(SITE_URL . '/admin/reports.php');
}

if (isset($_GET['remove_product']) && verifyCsrf($_GET['token'] ?? '')) {
    $pid = (int) $_GET['remove_product'];
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$pid]);
    $pdo->prepare('UPDATE reports SET status = "resolved" WHERE product_id = ?')->execute([$pid]);
    setFlash('success', 'Product removed and reports resolved.');
    redirect(SITE_URL . '/admin/reports.php');
}

$filter = $_GET['status'] ?? '';
$sql = "SELECT r.*, u.full_name AS reporter_name, p.title AS product_title
        FROM reports r
        JOIN users u ON r.reporter_id = u.id
        LEFT JOIN products p ON r.product_id = p.id";
$params = [];

if ($filter) {
    $sql .= ' WHERE r.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY r.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h1><i class="fas fa-flag"></i> Reports</h1>
        <div class="filter-tabs">
            <a href="?" class="<?= !$filter ? 'active' : '' ?>">All</a>
            <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=reviewed" class="<?= $filter === 'reviewed' ? 'active' : '' ?>">Reviewed</a>
            <a href="?status=resolved" class="<?= $filter === 'resolved' ? 'active' : '' ?>">Resolved</a>
        </div>
    </div>

    <?php if (empty($reports)): ?>
        <div class="empty-state"><i class="fas fa-flag"></i><h3>No reports found</h3></div>
    <?php else: ?>
        <?php foreach ($reports as $r): ?>
            <div class="report-card">
                <div class="report-header">
                    <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                    <small><?= timeAgo($r['created_at']) ?></small>
                </div>
                <p><strong>Reported by:</strong> <?= sanitize($r['reporter_name']) ?></p>
                <?php if ($r['product_title']): ?>
                    <p><strong>Product:</strong> <?= sanitize($r['product_title']) ?>
                        <?php if ($r['product_id']): ?>
                            <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $r['product_id'] ?>">View</a>
                            <a href="?remove_product=<?= $r['product_id'] ?>&token=<?= csrfToken() ?>"
                               class="btn btn-sm btn-danger" onclick="return confirm('Remove this product?')">Remove Product</a>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p><strong>Reason:</strong> <?= nl2br(sanitize($r['reason'])) ?></p>
                <?php if ($r['admin_note']): ?>
                    <p><strong>Admin Note:</strong> <?= sanitize($r['admin_note']) ?></p>
                <?php endif; ?>

                <?php if ($r['status'] === 'pending'): ?>
                    <form method="POST" class="report-action-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                        <textarea name="admin_note" placeholder="Admin note (optional)" rows="2"></textarea>
                        <div>
                            <button type="submit" name="status" value="reviewed" class="btn btn-sm btn-primary">Mark Reviewed</button>
                            <button type="submit" name="status" value="resolved" class="btn btn-sm btn-outline">Resolve</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

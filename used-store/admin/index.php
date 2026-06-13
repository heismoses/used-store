<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$stats = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_users,
        (SELECT COUNT(*) FROM users WHERE status = 'blocked') AS blocked_users,
        (SELECT COUNT(*) FROM products) AS total_products,
        (SELECT COUNT(*) FROM products WHERE status = 'pending') AS pending_products,
        (SELECT COUNT(*) FROM reports WHERE status = 'pending') AS pending_reports,
        (SELECT COUNT(*) FROM messages) AS total_messages"
)->fetch();

$recentUsers = $pdo->query('SELECT * FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
$pendingItems = $pdo->query(
    "SELECT p.*, u.full_name AS seller_name FROM products p
     JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.date_posted DESC LIMIT 5"
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card dash-stat">
            <i class="fas fa-users"></i>
            <div><h3><?= $stats['total_users'] ?></h3><p>Total Users</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-box"></i>
            <div><h3><?= $stats['total_products'] ?></h3><p>Total Products</p></div>
        </div>
        <div class="stat-card dash-stat warning">
            <i class="fas fa-clock"></i>
            <div><h3><?= $stats['pending_products'] ?></h3><p>Pending Approval</p></div>
        </div>
        <div class="stat-card dash-stat danger">
            <i class="fas fa-flag"></i>
            <div><h3><?= $stats['pending_reports'] ?></h3><p>Pending Reports</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-ban"></i>
            <div><h3><?= $stats['blocked_users'] ?></h3><p>Blocked Users</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-envelope"></i>
            <div><h3><?= $stats['total_messages'] ?></h3><p>Total Messages</p></div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>Pending Items for Review</h2>
            <?php if (empty($pendingItems)): ?>
                <p class="empty-text">No pending items.</p>
            <?php else: ?>
                <?php foreach ($pendingItems as $item): ?>
                    <div class="dash-item">
                        <div>
                            <h4><?= sanitize($item['title']) ?></h4>
                            <p>By <?= sanitize($item['seller_name']) ?> · <?= formatPrice($item['price']) ?></p>
                        </div>
                        <div class="actions">
                            <a href="<?= SITE_URL ?>/admin/products.php?approve=<?= $item['id'] ?>&token=<?= csrfToken() ?>"
                               class="btn btn-sm btn-primary">Approve</a>
                            <a href="<?= SITE_URL ?>/admin/products.php?reject=<?= $item['id'] ?>&token=<?= csrfToken() ?>"
                               class="btn btn-sm btn-danger">Reject</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="<?= SITE_URL ?>/admin/products.php" class="view-all">View All Products →</a>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2>Recent Users</h2>
            <?php foreach ($recentUsers as $u): ?>
                <div class="dash-item">
                    <img src="<?= getProfileImage($u['profile_picture']) ?>" alt="" class="conv-avatar">
                    <div>
                        <h4><?= sanitize($u['full_name']) ?></h4>
                        <p><?= sanitize($u['email']) ?> · <?= ucfirst($u['role']) ?></p>
                    </div>
                    <span class="status-badge status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span>
                </div>
            <?php endforeach; ?>
            <a href="<?= SITE_URL ?>/admin/users.php" class="view-all">Manage Users →</a>
        </div>
    </div>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

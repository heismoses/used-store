<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

$stats = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM products WHERE user_id = ?) AS total_items,
        (SELECT COUNT(*) FROM products WHERE user_id = ? AND status = 'approved') AS active_items,
        (SELECT COUNT(*) FROM products WHERE user_id = ? AND status = 'sold') AS sold_items,
        (SELECT COUNT(*) FROM favorites WHERE user_id = ?) AS favorites,
        (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0) AS unread_messages"
);
$stats->execute([$userId, $userId, $userId, $userId, $userId]);
$userStats = $stats->fetch();

$recentActivity = $pdo->prepare(
    'SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
);
$recentActivity->execute([$userId]);
$activities = $recentActivity->fetchAll();

$recentItems = $pdo->prepare(
    "SELECT p.*, c.name AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.user_id = ? ORDER BY p.date_posted DESC LIMIT 5"
);
$recentItems->execute([$userId]);
$items = $recentItems->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h1>Welcome, <?= sanitize($user['full_name']) ?>!</h1>
        <a href="<?= SITE_URL ?>/pages/post-item.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Post New Item
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card dash-stat">
            <i class="fas fa-box"></i>
            <div><h3><?= $userStats['total_items'] ?></h3><p>Total Items</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-check-circle"></i>
            <div><h3><?= $userStats['active_items'] ?></h3><p>Active Listings</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-handshake"></i>
            <div><h3><?= $userStats['sold_items'] ?></h3><p>Sold Items</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-heart"></i>
            <div><h3><?= $userStats['favorites'] ?></h3><p>Favorites</p></div>
        </div>
        <div class="stat-card dash-stat">
            <i class="fas fa-envelope"></i>
            <div><h3><?= $userStats['unread_messages'] ?></h3><p>Unread Messages</p></div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2><i class="fas fa-box"></i> Recent Listings</h2>
            <?php if (empty($items)): ?>
                <p class="empty-text">No items posted yet. <a href="<?= SITE_URL ?>/pages/post-item.php">Post your first item</a></p>
            <?php else: ?>
                <div class="dash-items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="dash-item">
                            <img src="<?= getProductImage($item['image']) ?>" alt="">
                            <div>
                                <h4><?= sanitize($item['title']) ?></h4>
                                <p><?= formatPrice($item['price']) ?> · <?= sanitize($item['category_name']) ?></p>
                            </div>
                            <span class="status-badge status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= SITE_URL ?>/dashboard/my-items.php" class="view-all">View All Items →</a>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2><i class="fas fa-history"></i> Recent Activity</h2>
            <?php if (empty($activities)): ?>
                <p class="empty-text">No activity yet.</p>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($activities as $act): ?>
                        <li>
                            <i class="fas fa-circle"></i>
                            <div>
                                <strong><?= sanitize($act['action']) ?></strong>
                                <?php if ($act['details']): ?>
                                    <span>— <?= sanitize($act['details']) ?></span>
                                <?php endif; ?>
                                <small><?= timeAgo($act['created_at']) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

</div><!-- close dashboard-layout -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

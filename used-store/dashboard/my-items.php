<?php
$pageTitle = 'My Items';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

if (isset($_GET['delete']) && verifyCsrf($_GET['token'] ?? '')) {
    $deleteId = (int) $_GET['delete'];
    $check = $pdo->prepare('SELECT id FROM products WHERE id = ? AND user_id = ?');
    $check->execute([$deleteId, $userId]);
    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$deleteId]);
        logActivity($userId, 'Deleted item', "Deleted product ID: $deleteId");
        setFlash('success', 'Item deleted successfully.');
    }
    redirect(SITE_URL . '/dashboard/my-items.php');
}

if (isset($_GET['mark_sold']) && verifyCsrf($_GET['token'] ?? '')) {
    $soldId = (int) $_GET['mark_sold'];
    $pdo->prepare('UPDATE products SET status = "sold" WHERE id = ? AND user_id = ?')
        ->execute([$soldId, $userId]);
    setFlash('success', 'Item marked as sold.');
    redirect(SITE_URL . '/dashboard/my-items.php');
}

$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.user_id = ? ORDER BY p.date_posted DESC"
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h1><i class="fas fa-box"></i> My Items (<?= count($items) ?>)</h1>
        <a href="<?= SITE_URL ?>/pages/post-item.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Post New Item
        </a>
    </div>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No items posted yet</h3>
            <a href="<?= SITE_URL ?>/pages/post-item.php" class="btn btn-primary">Post Your First Item</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="table-item">
                                    <img src="<?= getProductImage($item['image']) ?>" alt="">
                                    <span><?= sanitize($item['title']) ?></span>
                                </div>
                            </td>
                            <td><?= sanitize($item['category_name']) ?></td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td><span class="status-badge status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                            <td><?= $item['views'] ?></td>
                            <td><?= timeAgo($item['date_posted']) ?></td>
                            <td class="actions">
                                <?php if ($item['status'] === 'approved'): ?>
                                    <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($item['status'] !== 'sold'): ?>
                                    <a href="?mark_sold=<?= $item['id'] ?>&token=<?= csrfToken() ?>"
                                       class="btn btn-sm btn-outline" title="Mark Sold"
                                       onclick="return confirm('Mark this item as sold?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?= $item['id'] ?>&token=<?= csrfToken() ?>"
                                   class="btn btn-sm btn-danger" title="Delete"
                                   onclick="return confirm('Delete this item permanently?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

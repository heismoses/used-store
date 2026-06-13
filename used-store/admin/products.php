<?php
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['approve']) && verifyCsrf($_GET['token'] ?? '')) {
    $pid = (int) $_GET['approve'];
    $pdo->prepare('UPDATE products SET status = "approved" WHERE id = ?')->execute([$pid]);
    $prod = $pdo->prepare('SELECT user_id, title FROM products WHERE id = ?');
    $prod->execute([$pid]);
    if ($p = $prod->fetch()) {
        createNotification($p['user_id'], 'Item Approved', "Your listing \"{$p['title']}\" has been approved.", 'product');
    }
    setFlash('success', 'Product approved.');
    redirect(SITE_URL . '/admin/products.php');
}

if (isset($_GET['reject']) && verifyCsrf($_GET['token'] ?? '')) {
    $pid = (int) $_GET['reject'];
    $pdo->prepare('UPDATE products SET status = "rejected" WHERE id = ?')->execute([$pid]);
    setFlash('success', 'Product rejected.');
    redirect(SITE_URL . '/admin/products.php');
}

if (isset($_GET['delete']) && verifyCsrf($_GET['token'] ?? '')) {
    $pid = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$pid]);
    setFlash('success', 'Product deleted.');
    redirect(SITE_URL . '/admin/products.php');
}

$filter = $_GET['status'] ?? '';
$sql = "SELECT p.*, u.full_name AS seller_name, c.name AS category_name,
               (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
        FROM products p
        JOIN users u ON p.user_id = u.id
        JOIN categories c ON p.category_id = c.id";
$params = [];

if ($filter) {
    $sql .= ' WHERE p.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY p.date_posted DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h1><i class="fas fa-box"></i> Manage Products</h1>
        <div class="filter-tabs">
            <a href="?" class="<?= !$filter ? 'active' : '' ?>">All</a>
            <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
            <a href="?status=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">Rejected</a>
            <a href="?status=sold" class="<?= $filter === 'sold' ? 'active' : '' ?>">Sold</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div class="table-item">
                                <img src="<?= getProductImage($p['image']) ?>" alt="">
                                <span><?= sanitize($p['title']) ?></span>
                            </div>
                        </td>
                        <td><?= sanitize($p['seller_name']) ?></td>
                        <td><?= sanitize($p['category_name']) ?></td>
                        <td><?= formatPrice($p['price']) ?></td>
                        <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td><?= $p['views'] ?></td>
                        <td><?= timeAgo($p['date_posted']) ?></td>
                        <td class="actions">
                            <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($p['status'] === 'pending'): ?>
                                <a href="?approve=<?= $p['id'] ?>&token=<?= csrfToken() ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="?reject=<?= $p['id'] ?>&token=<?= csrfToken() ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                            <a href="?delete=<?= $p['id'] ?>&token=<?= csrfToken() ?>"
                               class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

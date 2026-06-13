<?php
$pageTitle = 'Favorites';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$stmt = $pdo->prepare(
    "SELECT p.*, f.created_at AS favorited_at, c.name AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
     FROM favorites f
     JOIN products p ON f.product_id = p.id
     JOIN categories c ON p.category_id = c.id
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-heart"></i> My Favorites</h1>
    <p class="page-subtitle"><?= count($favorites) ?> saved item<?= count($favorites) !== 1 ? 's' : '' ?></p>

    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <i class="fas fa-heart"></i>
            <h3>No favorites yet</h3>
            <p>Save items you like by clicking the heart icon on product pages.</p>
            <a href="<?= SITE_URL ?>/pages/marketplace.php" class="btn btn-primary">Browse Marketplace</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($favorites as $item): ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>" class="product-image">
                        <img src="<?= getProductImage($item['image']) ?>" alt="<?= sanitize($item['title']) ?>" loading="lazy">
                        <span class="product-condition"><?= ucfirst($item['condition_type']) ?></span>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($item['category_name']) ?></span>
                        <h3 class="product-title">
                            <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>"><?= sanitize($item['title']) ?></a>
                        </h3>
                        <p class="product-price"><?= formatPrice($item['price']) ?></p>
                        <p class="product-location"><i class="fas fa-map-marker-alt"></i> <?= sanitize($item['location']) ?></p>
                        <button class="btn btn-sm btn-outline favorite-btn active" data-product-id="<?= $item['id'] ?>">
                            <i class="fas fa-heart"></i> Remove
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$extraJs = 'item.js';
require_once __DIR__ . '/../includes/footer.php';
?>

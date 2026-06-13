<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    redirect(SITE_URL . '/pages/marketplace.php');
}

$stmt = $pdo->prepare(
    "SELECT p.*, u.full_name AS seller_name, u.email AS seller_email, u.phone AS seller_phone,
            u.profile_picture AS seller_avatar, u.id AS seller_id,
            c.name AS category_name, c.slug AS category_slug
     FROM products p
     JOIN users u ON p.user_id = u.id
     JOIN categories c ON p.category_id = c.id
     WHERE p.id = ?"
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product || ($product['status'] !== 'approved' && (!isLoggedIn() || ($_SESSION['user_id'] != $product['user_id'] && !isAdmin())))) {
    setFlash('error', 'Product not found or not available.');
    redirect(SITE_URL . '/pages/marketplace.php');
}

$pdo->prepare('UPDATE products SET views = views + 1 WHERE id = ?')->execute([$id]);
$product['views']++;

$imgStmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC');
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

$isFavorite = false;
if (isLoggedIn()) {
    $favStmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND product_id = ?');
    $favStmt->execute([$_SESSION['user_id'], $id]);
    $isFavorite = (bool) $favStmt->fetch();
}

$relatedStmt = $pdo->prepare(
    "SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
     FROM products p WHERE p.category_id = ? AND p.id != ? AND p.status = 'approved'
     ORDER BY RAND() LIMIT 4"
);
$relatedStmt->execute([$product['category_id'], $id]);
$related = $relatedStmt->fetchAll();

$pageTitle = $product['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container item-detail-page">
    <nav class="breadcrumb">
        <a href="<?= SITE_URL ?>/index.php">Home</a> /
        <a href="<?= SITE_URL ?>/pages/marketplace.php">Marketplace</a> /
        <a href="<?= SITE_URL ?>/pages/marketplace.php?category=<?= $product['category_slug'] ?>">
            <?= sanitize($product['category_name']) ?>
        </a> /
        <span><?= sanitize($product['title']) ?></span>
    </nav>

    <div class="item-detail-grid">
        <div class="item-gallery">
            <div class="main-image">
                <img id="mainImage" src="<?= getProductImage($images[0]['image_path'] ?? null) ?>"
                     alt="<?= sanitize($product['title']) ?>">
            </div>
            <?php if (count($images) > 1): ?>
                <div class="thumbnail-list">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= getProductImage($img['image_path']) ?>"
                             alt="Thumbnail" class="thumbnail"
                             onclick="document.getElementById('mainImage').src = this.src">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="item-info-panel">
            <span class="item-category"><?= sanitize($product['category_name']) ?></span>
            <h1><?= sanitize($product['title']) ?></h1>
            <p class="item-price"><?= formatPrice($product['price']) ?></p>

            <div class="item-badges">
                <span class="badge-condition"><?= ucfirst($product['condition_type']) ?></span>
                <span class="badge-views"><i class="fas fa-eye"></i> <?= $product['views'] ?> views</span>
                <span class="badge-date"><i class="fas fa-clock"></i> <?= timeAgo($product['date_posted']) ?></span>
            </div>

            <div class="item-details-list">
                <div><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?= sanitize($product['location']) ?></div>
                <div><i class="fas fa-tag"></i> <strong>Condition:</strong> <?= ucfirst($product['condition_type']) ?></div>
                <div><i class="fas fa-calendar"></i> <strong>Posted:</strong> <?= date('F j, Y', strtotime($product['date_posted'])) ?></div>
            </div>

            <div class="item-description">
                <h3>Description</h3>
                <p><?= nl2br(sanitize($product['description'])) ?></p>
            </div>

            <div class="item-actions">
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['seller_id']): ?>
                    <a href="<?= SITE_URL ?>/pages/messages.php?user=<?= $product['seller_id'] ?>&product=<?= $id ?>"
                       class="btn btn-primary btn-lg">
                        <i class="fas fa-comment"></i> Contact Seller
                    </a>
                    <button class="btn btn-outline btn-lg favorite-btn <?= $isFavorite ? 'active' : '' ?>"
                            data-product-id="<?= $id ?>" id="favoriteBtn">
                        <i class="fas fa-heart"></i> <?= $isFavorite ? 'Saved' : 'Save' ?>
                    </button>
                    <button class="btn btn-outline btn-lg" onclick="document.getElementById('reportModal').classList.add('show')">
                        <i class="fas fa-flag"></i> Report
                    </button>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login to Contact Seller
                    </a>
                <?php endif; ?>
            </div>

            <div class="seller-card">
                <img src="<?= getProfileImage($product['seller_avatar']) ?>" alt="Seller" class="seller-avatar">
                <div>
                    <h4><?= sanitize($product['seller_name']) ?></h4>
                    <p><i class="fas fa-envelope"></i> <?= sanitize($product['seller_email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= sanitize($product['seller_phone']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <section class="related-section">
            <h2>Related Items</h2>
            <div class="products-grid">
                <?php foreach ($related as $item): ?>
                    <div class="product-card">
                        <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>" class="product-image">
                            <img src="<?= getProductImage($item['image']) ?>" alt="<?= sanitize($item['title']) ?>" loading="lazy">
                        </a>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>"><?= sanitize($item['title']) ?></a>
                            </h3>
                            <p class="product-price"><?= formatPrice($item['price']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php if (isLoggedIn()): ?>
<div class="modal" id="reportModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Report Listing</h3>
            <button class="modal-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
        </div>
        <form id="reportForm">
            <input type="hidden" name="product_id" value="<?= $id ?>">
            <div class="form-group">
                <label>Reason for reporting</label>
                <textarea name="reason" required rows="4" placeholder="Describe the issue..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
$extraJs = 'item.js';
require_once __DIR__ . '/../includes/footer.php';
?>

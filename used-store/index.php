<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/functions.php';

$stmt = $pdo->query(
    "SELECT p.*, u.full_name AS seller_name, c.name AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
     FROM products p
     JOIN users u ON p.user_id = u.id
     JOIN categories c ON p.category_id = c.id
     WHERE p.status = 'approved'
     ORDER BY p.date_posted DESC
     LIMIT 8"
);
$featuredItems = $stmt->fetchAll();

$categories = getCategories();

$stats = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM products WHERE status = 'approved') AS total_products,
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_users,
        (SELECT COUNT(*) FROM categories) AS total_categories"
)->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Buy & Sell Used Items<br><span>Near You</span></h1>
        <p>Discover great deals on phones, electronics, furniture, vehicles and more in your community.</p>
        <div class="hero-actions">
            <a href="<?= SITE_URL ?>/pages/marketplace.php" class="btn btn-primary btn-lg">
                <i class="fas fa-search"></i> Browse Marketplace
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/pages/post-item.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-plus"></i> Sell an Item
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-user-plus"></i> Join Free
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat-card">
            <i class="fas fa-box"></i>
            <h3><?= number_format($stats['total_products']) ?></h3>
            <p>Active Listings</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3><?= number_format($stats['total_users']) ?></h3>
            <p>Community Members</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-tags"></i>
            <h3><?= number_format($stats['total_categories']) ?></h3>
            <p>Categories</p>
        </div>
    </div>
</section>

<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Browse by Category</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/pages/marketplace.php?category=<?= $cat['slug'] ?>" class="category-card">
                    <i class="fas <?= $cat['icon'] ?>"></i>
                    <span><?= sanitize($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Latest Listings</h2>
            <a href="<?= SITE_URL ?>/pages/marketplace.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredItems as $item): ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>" class="product-image">
                        <img src="<?= getProductImage($item['image']) ?>" alt="<?= sanitize($item['title']) ?>" loading="lazy">
                        <span class="product-condition"><?= ucfirst($item['condition_type']) ?></span>
                    </a>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="<?= SITE_URL ?>/pages/item.php?id=<?= $item['id'] ?>"><?= sanitize($item['title']) ?></a>
                        </h3>
                        <p class="product-price"><?= formatPrice($item['price']) ?></p>
                        <p class="product-location"><i class="fas fa-map-marker-alt"></i> <?= sanitize($item['location']) ?></p>
                        <p class="product-meta">
                            <span><i class="fas fa-eye"></i> <?= $item['views'] ?></span>
                            <span><?= timeAgo($item['date_posted']) ?></span>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <i class="fas fa-user-plus"></i>
                <h3>Create Account</h3>
                <p>Sign up for free with your email and start buying or selling.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <i class="fas fa-camera"></i>
                <h3>Post Your Item</h3>
                <p>Upload photos, set your price, and describe your item.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <i class="fas fa-comments"></i>
                <h3>Connect & Sell</h3>
                <p>Chat with buyers, negotiate, and complete your sale.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

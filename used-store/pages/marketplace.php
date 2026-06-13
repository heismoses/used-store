<?php
$pageTitle = 'Marketplace';
require_once __DIR__ . '/../includes/functions.php';

$search     = trim($_GET['search'] ?? '');
$category   = trim($_GET['category'] ?? '');
$minPrice   = $_GET['min_price'] ?? '';
$maxPrice   = $_GET['max_price'] ?? '';
$location   = trim($_GET['location'] ?? '');
$sort       = $_GET['sort'] ?? 'newest';
$page       = max(1, (int) ($_GET['page'] ?? 1));
$perPage    = 12;

$where  = ["p.status = 'approved'"];
$params = [];

if ($search !== '') {
    $where[]  = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category !== '') {
    $where[]  = 'c.slug = ?';
    $params[] = $category;
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $where[]  = 'p.price >= ?';
    $params[] = (float) $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $where[]  = 'p.price <= ?';
    $params[] = (float) $maxPrice;
}
if ($location !== '') {
    $where[]  = 'p.location LIKE ?';
    $params[] = "%$location%";
}

$whereClause = implode(' AND ', $where);

$orderMap = [
    'newest'     => 'p.date_posted DESC',
    'oldest'     => 'p.date_posted ASC',
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'popular'    => 'p.views DESC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['newest'];

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pagination = paginate($total, $perPage, $page);

$sql = "SELECT p.*, u.full_name AS seller_name, c.name AS category_name, c.slug AS category_slug,
               (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
        FROM products p
        JOIN users u ON p.user_id = u.id
        JOIN categories c ON p.category_id = c.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = getCategories();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="marketplace-page">
    <aside class="filter-sidebar" id="filterSidebar">
        <div class="filter-header">
            <h3><i class="fas fa-filter"></i> Filters</h3>
            <button class="filter-close" id="filterClose">&times;</button>
        </div>
        <form method="GET" action="" class="filter-form" id="filterForm">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="<?= sanitize($search) ?>"
                       placeholder="Search items...">
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['slug'] ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price Range (KES)</label>
                <div class="price-range">
                    <input type="number" name="min_price" placeholder="Min" value="<?= sanitize($minPrice) ?>" min="0">
                    <span>—</span>
                    <input type="number" name="max_price" placeholder="Max" value="<?= sanitize($maxPrice) ?>" min="0">
                </div>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= sanitize($location) ?>"
                       placeholder="City or area">
            </div>
            <div class="form-group">
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="<?= SITE_URL ?>/pages/marketplace.php" class="btn btn-outline btn-block">Clear Filters</a>
        </form>
    </aside>

    <div class="marketplace-content">
        <div class="marketplace-header">
            <div>
                <h1>Marketplace</h1>
                <p><?= $total ?> item<?= $total !== 1 ? 's' : '' ?> found</p>
            </div>
            <button class="btn btn-outline filter-toggle" id="filterToggle">
                <i class="fas fa-filter"></i> Filters
            </button>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>No items found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $item): ?>
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
                            <p class="product-meta">
                                <span><i class="fas fa-eye"></i> <?= $item['views'] ?></span>
                                <span><?= timeAgo($item['date_posted']) ?></span>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = $_GET;
                    for ($i = 1; $i <= $pagination['total_pages']; $i++):
                        $queryParams['page'] = $i;
                        $url = '?' . http_build_query($queryParams);
                    ?>
                        <a href="<?= $url ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$extraJs = 'marketplace.js';
require_once __DIR__ . '/../includes/footer.php';
?>

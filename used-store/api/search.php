<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$search     = trim($_GET['search'] ?? '');
$category   = trim($_GET['category'] ?? '');
$minPrice   = $_GET['min_price'] ?? '';
$maxPrice   = $_GET['max_price'] ?? '';
$location   = trim($_GET['location'] ?? '');
$sort       = $_GET['sort'] ?? 'newest';

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
    'newest' => 'p.date_posted DESC', 'oldest' => 'p.date_posted ASC',
    'price_low' => 'p.price ASC', 'price_high' => 'p.price DESC', 'popular' => 'p.views DESC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['newest'];

$sql = "SELECT p.id, p.title, p.price, p.location, p.condition_type, p.views, p.date_posted,
               c.name AS category_name,
               (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE $whereClause ORDER BY $orderBy LIMIT 24";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

foreach ($products as &$p) {
    $p['price_formatted'] = formatPrice($p['price']);
    $p['image_url'] = getProductImage($p['image']);
    $p['time_ago'] = timeAgo($p['date_posted']);
    $p['url'] = SITE_URL . '/pages/item.php?id=' . $p['id'];
}

echo json_encode(['success' => true, 'count' => count($products), 'products' => $products]);

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = (int) ($data['product_id'] ?? $_POST['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

$userId = $_SESSION['user_id'];

$check = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND product_id = ?');
$check->execute([$userId, $productId]);

if ($check->fetch()) {
    $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND product_id = ?')->execute([$userId, $productId]);
    echo json_encode(['success' => true, 'favorited' => false, 'message' => 'Removed from favorites']);
} else {
    $pdo->prepare('INSERT INTO favorites (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    logActivity($userId, 'Added favorite', "Product ID: $productId");
    echo json_encode(['success' => true, 'favorited' => true, 'message' => 'Added to favorites']);
}

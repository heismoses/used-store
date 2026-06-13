<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = (int) ($data['product_id'] ?? 0);
$reason = trim($data['reason'] ?? '');

if (!$productId || strlen($reason) < 5) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid reason']);
    exit;
}

$pdo->prepare('INSERT INTO reports (reporter_id, product_id, reason) VALUES (?, ?, ?)')
    ->execute([$_SESSION['user_id'], $productId, sanitize($reason)]);

createNotification(1, 'New Report', 'A listing has been reported', 'report', SITE_URL . '/admin/reports.php');

echo json_encode(['success' => true, 'message' => 'Report submitted. Thank you.']);

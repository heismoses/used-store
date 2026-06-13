<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title, message, type, link, is_read, created_at
     FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20'
);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

echo json_encode($notifications);

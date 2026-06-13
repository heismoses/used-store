<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['messages' => 0, 'notifications' => 0]);
    exit;
}

echo json_encode([
    'messages'      => getUnreadMessageCount($_SESSION['user_id']),
    'notifications' => getUnreadNotificationCount($_SESSION['user_id']),
]);

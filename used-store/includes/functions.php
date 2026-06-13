<?php
/**
 * Core Helper Functions
 * Used Items Marketplace
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please login to continue.';
        redirect(SITE_URL . '/auth/login.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
        redirect(SITE_URL . '/index.php');
    }
}

function getCurrentUser(): ?array
{
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND status = "active"');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string
{
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

function formatPrice(float $price): string
{
    return 'KES ' . number_format($price, 2);
}

function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

function uploadImage(array $file, string $directory, string $prefix = 'img'): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return null;
    }

    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $ext;
    $targetDir = UPLOAD_PATH . $directory . '/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        return $filename;
    }

    return null;
}

function getProductImage(?string $path, string $type = 'products'): string
{
    if ($path && $path !== 'default-product.png' && file_exists(UPLOAD_PATH . $type . '/' . $path)) {
        return SITE_URL . '/uploads/' . $type . '/' . $path;
    }
    return SITE_URL . '/assets/images/default-product.svg';
}

function getProfileImage(?string $path): string
{
    if ($path && $path !== 'default-avatar.png' && file_exists(UPLOAD_PATH . 'profiles/' . $path)) {
        return SITE_URL . '/uploads/profiles/' . $path;
    }
    return SITE_URL . '/assets/images/default-avatar.svg';
}

function logActivity(int $userId, string $action, ?string $details = null): void
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action, details) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $details]);
}

function createNotification(int $userId, string $title, string $message, string $type = 'system', ?string $link = null): void
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

function getUnreadNotificationCount(int $userId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getUnreadMessageCount(int $userId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function getCategories(): array
{
    global $pdo;
    return $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone(string $phone): bool
{
    return preg_match('/^[0-9+\-\s()]{7,20}$/', $phone);
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function paginate(int $total, int $perPage, int $currentPage): array
{
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
    ];
}

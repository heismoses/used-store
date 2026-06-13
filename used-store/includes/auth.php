<?php
/**
 * Authentication Handlers
 * Used Items Marketplace
 */

require_once __DIR__ . '/functions.php';

function registerUser(array $data): array
{
    global $pdo;

    $errors = [];

    if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    if (!validateEmail($data['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!validatePhone($data['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }
    if (strlen($data['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Email address is already registered.']];
    }

    $profilePicture = 'default-avatar.png';
    if (isset($data['profile_picture']) && $data['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($data['profile_picture'], 'profiles', 'profile');
        if ($uploaded) {
            $profilePicture = $uploaded;
        }
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, phone, password, profile_picture) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        sanitize($data['full_name']),
        sanitize($data['email']),
        sanitize($data['phone']),
        $hashedPassword,
        $profilePicture,
    ]);

    $userId = (int) $pdo->lastInsertId();
    logActivity($userId, 'Registered', 'New user account created');

    return ['success' => true, 'user_id' => $userId];
}

function loginUser(string $email, string $password): array
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([sanitize($email)]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    if ($user['status'] === 'blocked') {
        return ['success' => false, 'error' => 'Your account has been blocked. Contact support.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];

    logActivity($user['id'], 'Logged in', 'User logged into the system');

    return ['success' => true, 'role' => $user['role']];
}

function logoutUser(): void
{
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'Logged out', 'User logged out of the system');
    }
    session_unset();
    session_destroy();
}

function updateProfile(int $userId, array $data): array
{
    global $pdo;

    $errors = [];

    if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    if (!validatePhone($data['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $profilePicture = null;
    if (isset($data['profile_picture']) && $data['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($data['profile_picture'], 'profiles', 'profile');
        if ($uploaded) {
            $profilePicture = $uploaded;
        } else {
            $errors[] = 'Failed to upload profile picture.';
            return ['success' => false, 'errors' => $errors];
        }
    }

    if ($profilePicture) {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, profile_picture = ? WHERE id = ?');
        $stmt->execute([sanitize($data['full_name']), sanitize($data['phone']), $profilePicture, $userId]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
        $stmt->execute([sanitize($data['full_name']), sanitize($data['phone']), $userId]);
    }

    if (!empty($data['new_password'])) {
        if (strlen($data['new_password']) < 6) {
            return ['success' => false, 'errors' => ['New password must be at least 6 characters.']];
        }
        if ($data['new_password'] !== $data['confirm_password']) {
            return ['success' => false, 'errors' => ['Passwords do not match.']];
        }
        $hashed = password_hash($data['new_password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hashed, $userId]);
    }

    $_SESSION['user_name'] = sanitize($data['full_name']);
    logActivity($userId, 'Updated profile', 'Profile information updated');

    return ['success' => true];
}

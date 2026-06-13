<?php
$pageTitle = 'Register';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $data = [
            'full_name'        => $_POST['full_name'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'phone'            => $_POST['phone'] ?? '',
            'password'         => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'profile_picture'  => $_FILES['profile_picture'] ?? null,
        ];

        $result = registerUser($data);
        if ($result['success']) {
            setFlash('success', 'Account created successfully! Please login.');
            redirect(SITE_URL . '/auth/login.php');
        } else {
            $errors = $result['errors'];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card auth-card-wide">
        <div class="auth-header">
            <i class="fas fa-user-plus auth-logo"></i>
            <h1>Create Account</h1>
            <p>Join UsedStore and start buying & selling</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="auth-form" novalidate>
            <?= csrfField() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="full_name" name="full_name" required minlength="2"
                           value="<?= sanitize($_POST['full_name'] ?? '') ?>"
                           placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= sanitize($_POST['email'] ?? '') ?>"
                           placeholder="john@example.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?= sanitize($_POST['phone'] ?? '') ?>"
                           placeholder="0712345678">
                </div>
                <div class="form-group">
                    <label for="profile_picture"><i class="fas fa-camera"></i> Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Min 6 characters">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Repeat password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= SITE_URL ?>/auth/login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

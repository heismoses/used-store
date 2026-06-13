<?php
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? SITE_URL . '/admin/index.php' : SITE_URL . '/dashboard/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            setFlash('success', 'Welcome back, ' . $_SESSION['user_name'] . '!');
            redirect($result['role'] === 'admin'
                ? SITE_URL . '/admin/index.php'
                : SITE_URL . '/dashboard/index.php');
        } else {
            $error = $result['error'];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-store auth-logo"></i>
            <h1>Welcome Back</h1>
            <p>Login to your UsedStore account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" required
                       value="<?= sanitize($_POST['email'] ?? '') ?>"
                       placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php">Register here</a></p>
            <p class="demo-credentials">
                <small>Demo Admin: admin@usedstore.com / password<br>
                Demo User: john@example.com / password<br>
                Demo User: jane@example.com / password</small>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<aside class="dashboard-sidebar admin-sidebar">
    <div class="sidebar-user">
        <i class="fas fa-shield-alt admin-icon"></i>
        <h3>Admin Panel</h3>
        <p><?= sanitize($_SESSION['user_name']) ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= SITE_URL ?>/admin/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <a href="<?= SITE_URL ?>/admin/products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Manage Products
        </a>
        <a href="<?= SITE_URL ?>/admin/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="<?= SITE_URL ?>/admin/reports.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-flag"></i> Reports
        </a>
        <a href="<?= SITE_URL ?>/index.php">
            <i class="fas fa-home"></i> Back to Site
        </a>
    </nav>
</aside>
<div class="dashboard-layout">

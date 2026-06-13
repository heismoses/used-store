<aside class="dashboard-sidebar">
    <div class="sidebar-user">
        <img src="<?= getProfileImage($user['profile_picture'] ?? 'default-avatar.png') ?>" alt="Profile">
        <h3><?= sanitize($user['full_name'] ?? $_SESSION['user_name']) ?></h3>
        <p><?= sanitize($user['email'] ?? $_SESSION['user_email']) ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= SITE_URL ?>/dashboard/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Overview
        </a>
        <a href="<?= SITE_URL ?>/dashboard/profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user-edit"></i> Edit Profile
        </a>
        <a href="<?= SITE_URL ?>/dashboard/my-items.php" class="<?= basename($_SERVER['PHP_SELF']) === 'my-items.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> My Items
        </a>
        <a href="<?= SITE_URL ?>/pages/messages.php">
            <i class="fas fa-envelope"></i> Messages
        </a>
        <a href="<?= SITE_URL ?>/pages/favorites.php">
            <i class="fas fa-heart"></i> Favorites
        </a>
        <a href="<?= SITE_URL ?>/dashboard/activity.php" class="<?= basename($_SERVER['PHP_SELF']) === 'activity.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i> Activity History
        </a>
        <a href="<?= SITE_URL ?>/pages/post-item.php">
            <i class="fas fa-plus-circle"></i> Post Item
        </a>
    </nav>
</aside>
<div class="dashboard-layout">

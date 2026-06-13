<?php
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? SITE_NAME;
$unreadNotifications = $currentUser ? getUnreadNotificationCount($currentUser['id']) : 0;
$unreadMessages = $currentUser ? getUnreadMessageCount($currentUser['id']) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if (isset($extraCss)): ?>
        <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $extraCss ?>">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
                <i class="fas fa-store"></i>
                <span>UsedStore</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>

            <div class="nav-menu" id="navMenu">
                <a href="<?= SITE_URL ?>/pages/marketplace.php" class="nav-link">
                    <i class="fas fa-shopping-bag"></i> Marketplace
                </a>

                <?php if (isLoggedIn()): ?>
                    <a href="<?= SITE_URL ?>/pages/post-item.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i> Sell Item
                    </a>
                    <a href="<?= SITE_URL ?>/pages/favorites.php" class="nav-link">
                        <i class="fas fa-heart"></i> Favorites
                    </a>
                    <a href="<?= SITE_URL ?>/pages/messages.php" class="nav-link">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadMessages > 0): ?>
                            <span class="badge"><?= $unreadMessages ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= SITE_URL ?>/dashboard/index.php" class="nav-link">
                        <i class="fas fa-user-circle"></i> Dashboard
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= SITE_URL ?>/admin/index.php" class="nav-link admin-link">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                    <?php endif; ?>

                    <div class="nav-notifications">
                        <button class="notif-btn" id="notifBtn" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="badge" id="notifBadge"><?= $unreadNotifications ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="notif-dropdown" id="notifDropdown"></div>
                    </div>

                    <a href="<?= SITE_URL ?>/auth/logout.php" class="nav-link logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="nav-link btn-nav-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>

                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <?php
    $flashSuccess = getFlash('success');
    $flashError = getFlash('error');
    if ($flashSuccess): ?>
        <div class="alert alert-success" id="flashAlert">
            <i class="fas fa-check-circle"></i> <?= $flashSuccess ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif;
    if ($flashError): ?>
        <div class="alert alert-error" id="flashAlert">
            <i class="fas fa-exclamation-circle"></i> <?= $flashError ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <main class="main-content">

    </main>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3><i class="fas fa-store"></i> UsedStore Marketplace</h3>
                <p>Buy and sell used items in your community. Safe, simple, and free.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/pages/marketplace.php">Browse Items</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/post-item.php">Sell an Item</a></li>
                    <li><a href="<?= SITE_URL ?>/auth/register.php">Create Account</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Categories</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/pages/marketplace.php?category=phones">Phones</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/marketplace.php?category=electronics">Electronics</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/marketplace.php?category=vehicles">Vehicles</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p><i class="fas fa-envelope"></i> support@usedstore.com</p>
                <p><i class="fas fa-phone"></i> +254 700 000 000</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> UsedStore Marketplace. All rights reserved. | Final Year Project</p>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($extraJs)): ?>
        <script src="<?= SITE_URL ?>/assets/js/<?= $extraJs ?>"></script>
    <?php endif; ?>
</body>
</html>

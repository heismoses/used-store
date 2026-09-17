CREATE DATABASE IF NOT EXISTS used_store_marketplace
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE used_store_marketplace;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(20) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    role            ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status          ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CATEGORIES TABLE

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    slug        VARCHAR(50) NOT NULL UNIQUE,
    icon        VARCHAR(50) DEFAULT 'fa-tag',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PRODUCTS TABLE--
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    category_id INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price       DECIMAL(10, 2) NOT NULL,
    location    VARCHAR(150) NOT NULL,
    condition_type ENUM('new', 'used') NOT NULL DEFAULT 'used',
    status      ENUM('pending', 'approved', 'rejected', 'sold') NOT NULL DEFAULT 'pending',
    views       INT NOT NULL DEFAULT 0,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_location (location),
    INDEX idx_date_posted (date_posted)
) ENGINE=InnoDB;

-- PRODUCT IMAGES TABLE--

CREATE TABLE product_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    product_id  INT NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_primary  TINYINT(1) NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- MESSAGES TABLE (Chat System)

CREATE TABLE messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sender_id       INT NOT NULL,
    receiver_id     INT NOT NULL,
    product_id      INT DEFAULT NULL,
    message         TEXT NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_conversation (sender_id, receiver_id)
) ENGINE=InnoDB;

-- FAVORITES / WISHLIST TABLE--

CREATE TABLE favorites (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id)
) ENGINE=InnoDB;

-- REPORTS TABLE--

CREATE TABLE reports (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    product_id  INT DEFAULT NULL,
    user_id     INT DEFAULT NULL,
    reason      TEXT NOT NULL,
    status      ENUM('pending', 'reviewed', 'resolved') NOT NULL DEFAULT 'pending',
    admin_note  TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- NOTIFICATIONS TABLE

CREATE TABLE notifications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    type        ENUM('message', 'product', 'system', 'report') NOT NULL DEFAULT 'system',
    link        VARCHAR(255) DEFAULT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ACTIVITY LOG TABLE--

CREATE TABLE activity_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    action      VARCHAR(100) NOT NULL,
    details     TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- SEED DATA: Categories--

INSERT INTO categories (name, slug, icon) VALUES
    ('Phones',       'phones',       'fa-mobile-alt'),
    ('Electronics',  'electronics',  'fa-laptop'),
    ('Clothes',      'clothes',      'fa-tshirt'),
    ('Furniture',    'furniture',    'fa-couch'),
    ('Vehicles',     'vehicles',     'fa-car'),
    ('Books',        'books',        'fa-book'),
    ('Other Items',  'other',        'fa-box');

-- ============================================================
-- SEED DATA: Default Admin Account
-- Password for all demo accounts: password
-- (hashed with bcrypt via password_hash)
-- ============================================================
INSERT INTO users (full_name, email, phone, password, role, status) VALUES
    ('System Administrator', 'admin@usedstore.com', '0700000000',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'admin', 'active');

-- ============================================================
-- SEED DATA: Sample Users
-- ============================================================
INSERT INTO users (full_name, email, phone, password, role, status) VALUES
    ('John Doe',    'john@example.com',  '0712345678',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active'),
    ('Jane Smith',  'jane@example.com',  '0723456789',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active');

-- ============================================================
-- SEED DATA: Sample Products
-- ============================================================
INSERT INTO products (user_id, category_id, title, description, price, location, condition_type, status, views) VALUES
    (2, 1, 'iPhone 13 Pro - 128GB', 'Excellent condition iPhone 13 Pro. Battery health 92%. Includes original box and charger.', 85000.00, 'Nairobi', 'used', 'approved', 45),
    (2, 2, 'Samsung 55" Smart TV', 'Crystal UHD 4K Smart TV. Barely used, purchased 6 months ago.', 55000.00, 'Nairobi', 'used', 'approved', 32),
    (3, 3, 'Designer Jacket - Size M', 'Brand new designer jacket, never worn. Tags still attached.', 3500.00, 'Mombasa', 'new', 'approved', 18),
    (3, 4, 'Office Desk with Drawers', 'Solid wood office desk with 3 drawers. Good condition.', 12000.00, 'Kisumu', 'used', 'approved', 27),
    (2, 6, 'Programming Books Bundle', 'Collection of 5 programming books including Python, JavaScript, and PHP.', 2500.00, 'Nairobi', 'used', 'approved', 15),
    (3, 5, 'Toyota Corolla 2018', 'Well maintained Toyota Corolla. Full service history. 65,000 km.', 1200000.00, 'Nakuru', 'used', 'approved', 89);

-- ============================================================
-- SEED DATA: Sample Product Images (placeholder paths)
-- ============================================================
INSERT INTO product_images (product_id, image_path, is_primary) VALUES
    (1, 'default-product.png', 1),
    (2, 'default-product.png', 1),
    (3, 'default-product.png', 1),
    (4, 'default-product.png', 1),
    (5, 'default-product.png', 1),
    (6, 'default-product.png', 1);

-- ============================================================
-- SEED DATA: Sample Messages
-- ============================================================
INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES
    (3, 2, 1, 'Hi, is the iPhone still available? Can you do KES 80,000?'),
    (2, 3, 1, 'Yes it is available. The lowest I can go is KES 82,000.'),
    (2, 3, 3, 'Hello Jane, thanks for your interest in the jacket!');

-- ============================================================
-- SEED DATA: Sample Notifications
-- ============================================================
INSERT INTO notifications (user_id, title, message, type, link) VALUES
    (2, 'New Message', 'Jane Smith sent you a message about iPhone 13 Pro', 'message', 'pages/messages.php?user=3'),
    (3, 'New Reply', 'John Doe replied to your message', 'message', 'pages/messages.php?user=2');

<?php
$pageTitle = 'Post Item';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$categories = getCategories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId  = (int) ($_POST['category_id'] ?? 0);
        $price       = (float) ($_POST['price'] ?? 0);
        $location    = trim($_POST['location'] ?? '');
        $condition   = $_POST['condition_type'] ?? 'used';

        if (strlen($title) < 3) $errors[] = 'Title must be at least 3 characters.';
        if (strlen($description) < 10) $errors[] = 'Description must be at least 10 characters.';
        if ($categoryId <= 0) $errors[] = 'Please select a category.';
        if ($price <= 0) $errors[] = 'Please enter a valid price.';
        if (empty($location)) $errors[] = 'Location is required.';
        if (!in_array($condition, ['new', 'used'])) $errors[] = 'Invalid condition.';

        if (empty($_FILES['images']['name'][0])) {
            $errors[] = 'Please upload at least one image.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'INSERT INTO products (user_id, category_id, title, description, price, location, condition_type, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, "pending")'
            );
            $stmt->execute([
                $_SESSION['user_id'], $categoryId, sanitize($title),
                sanitize($description), $price, sanitize($location), $condition,
            ]);
            $productId = (int) $pdo->lastInsertId();

            $uploaded = 0;
            foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name'     => $_FILES['images']['name'][$i],
                        'type'     => $_FILES['images']['type'][$i],
                        'tmp_name' => $tmpName,
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i],
                    ];
                    $filename = uploadImage($file, 'products', 'product');
                    if ($filename) {
                        $isPrimary = ($uploaded === 0) ? 1 : 0;
                        $pdo->prepare('INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)')
                            ->execute([$productId, $filename, $isPrimary]);
                        $uploaded++;
                    }
                }
            }

            logActivity($_SESSION['user_id'], 'Posted item', "Posted: $title");
            createNotification($_SESSION['user_id'], 'Item Posted', "Your listing \"$title\" is pending approval.", 'product');

            setFlash('success', 'Item posted successfully! It will be visible after admin approval.');
            redirect(SITE_URL . '/dashboard/my-items.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container form-page">
    <div class="form-card">
        <h1><i class="fas fa-plus-circle"></i> Post an Item for Sale</h1>
        <p class="form-subtitle">Fill in the details below to list your item on the marketplace.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="post-form" id="postForm" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label for="title">Item Title *</label>
                <input type="text" id="title" name="title" required minlength="3"
                       value="<?= sanitize($_POST['title'] ?? '') ?>"
                       placeholder="e.g. iPhone 13 Pro - 128GB">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required minlength="10" rows="5"
                          placeholder="Describe your item in detail..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="condition_type">Condition *</label>
                    <select id="condition_type" name="condition_type" required>
                        <option value="used" <?= (($_POST['condition_type'] ?? '') === 'used') ? 'selected' : '' ?>>Used</option>
                        <option value="new" <?= (($_POST['condition_type'] ?? '') === 'new') ? 'selected' : '' ?>>New</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (KES) *</label>
                    <input type="number" id="price" name="price" required min="1" step="0.01"
                           value="<?= sanitize($_POST['price'] ?? '') ?>" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" required
                           value="<?= sanitize($_POST['location'] ?? '') ?>"
                           placeholder="e.g. Nairobi, Westlands">
                </div>
            </div>

            <div class="form-group">
                <label for="images">Upload Images * (Max 5, 5MB each)</label>
                <div class="image-upload-area" id="imageUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Drag & drop images here or click to browse</p>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple required>
                </div>
                <div class="image-preview" id="imagePreview"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane"></i> Post Item
            </button>
        </form>
    </div>
</div>

<?php
$extraJs = 'post-item.js';
require_once __DIR__ . '/../includes/footer.php';
?>

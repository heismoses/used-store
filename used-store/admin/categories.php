<?php
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-tag');
        if ($name) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
            $pdo->prepare('INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)')
                ->execute([sanitize($name), $slug, sanitize($icon)]);
            setFlash('success', 'Category added.');
        }
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-tag');
        if ($id && $name) {
            $pdo->prepare('UPDATE categories SET name = ?, icon = ? WHERE id = ?')
                ->execute([sanitize($name), sanitize($icon), $id]);
            setFlash('success', 'Category updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $count->execute([$id]);
        if ((int) $count->fetchColumn() === 0) {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            setFlash('success', 'Category deleted.');
        } else {
            setFlash('error', 'Cannot delete category with existing products.');
        }
    }
    redirect(SITE_URL . '/admin/categories.php');
}

$categories = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
     FROM categories c ORDER BY c.name'
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <h1><i class="fas fa-tags"></i> Manage Categories</h1>

    <div class="form-card" style="margin-bottom: 2rem;">
        <h3>Add New Category</h3>
        <form method="POST" class="inline-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Category name" required>
            <input type="text" name="icon" placeholder="FontAwesome icon (e.g. fa-tag)" value="fa-tag">
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><i class="fas <?= $cat['icon'] ?>"></i></td>
                        <td><?= sanitize($cat['name']) ?></td>
                        <td><?= sanitize($cat['slug']) ?></td>
                        <td><?= $cat['product_count'] ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-outline" onclick="editCategory(<?= $cat['id'] ?>, '<?= sanitize($cat['name']) ?>', '<?= $cat['icon'] ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($cat['product_count'] == 0): ?>
                                <form method="POST" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button class="modal-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="editName" required>
            </div>
            <div class="form-group">
                <label>Icon</label>
                <input type="text" name="icon" id="editIcon">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

</div>
<script>
function editCategory(id, name, icon) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editIcon').value = icon;
    document.getElementById('editModal').classList.add('show');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

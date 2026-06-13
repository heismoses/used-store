<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if (isset($_GET['block']) && verifyCsrf($_GET['token'] ?? '')) {
    $uid = (int) $_GET['block'];
    if ($uid !== $_SESSION['user_id']) {
        $pdo->prepare('UPDATE users SET status = "blocked" WHERE id = ? AND role != "admin"')->execute([$uid]);
        setFlash('success', 'User blocked.');
    }
    redirect(SITE_URL . '/admin/users.php');
}

if (isset($_GET['unblock']) && verifyCsrf($_GET['token'] ?? '')) {
    $uid = (int) $_GET['unblock'];
    $pdo->prepare('UPDATE users SET status = "active" WHERE id = ?')->execute([$uid]);
    setFlash('success', 'User unblocked.');
    redirect(SITE_URL . '/admin/users.php');
}

if (isset($_GET['delete']) && verifyCsrf($_GET['token'] ?? '')) {
    $uid = (int) $_GET['delete'];
    if ($uid !== $_SESSION['user_id']) {
        $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"')->execute([$uid]);
        setFlash('success', 'User deleted.');
    }
    redirect(SITE_URL . '/admin/users.php');
}

$search = trim($_GET['search'] ?? '');
$sql = 'SELECT u.*, (SELECT COUNT(*) FROM products WHERE user_id = u.id) AS item_count FROM users u';
$params = [];

if ($search) {
    $sql .= ' WHERE u.full_name LIKE ? OR u.email LIKE ?';
    $params = ["%$search%", "%$search%"];
}
$sql .= ' ORDER BY u.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <div class="dashboard-header">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search users..." value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="table-item">
                                <img src="<?= getProfileImage($u['profile_picture']) ?>" alt="">
                                <span><?= sanitize($u['full_name']) ?></span>
                            </div>
                        </td>
                        <td><?= sanitize($u['email']) ?></td>
                        <td><?= sanitize($u['phone']) ?></td>
                        <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= $u['item_count'] ?></td>
                        <td><span class="status-badge status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td class="actions">
                            <?php if ($u['role'] !== 'admin' && $u['id'] !== $_SESSION['user_id']): ?>
                                <?php if ($u['status'] === 'active'): ?>
                                    <a href="?block=<?= $u['id'] ?>&token=<?= csrfToken() ?>"
                                       class="btn btn-sm btn-warning" onclick="return confirm('Block this user?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?unblock=<?= $u['id'] ?>&token=<?= csrfToken() ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-unlock"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?= $u['id'] ?>&token=<?= csrfToken() ?>"
                                   class="btn btn-sm btn-danger" onclick="return confirm('Delete this user permanently?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

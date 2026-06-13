<?php
$pageTitle = 'Edit Profile';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $result = updateProfile($user['id'], [
            'full_name'        => $_POST['full_name'] ?? '',
            'phone'            => $_POST['phone'] ?? '',
            'new_password'     => $_POST['new_password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'profile_picture'  => $_FILES['profile_picture'] ?? null,
        ]);

        if ($result['success']) {
            setFlash('success', 'Profile updated successfully!');
            redirect(SITE_URL . '/dashboard/profile.php');
        } else {
            $errors = $result['errors'];
        }
    }
    $user = getCurrentUser();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<div class="dashboard-content">
    <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <?= csrfField() ?>

            <div class="profile-avatar-section">
                <img src="<?= getProfileImage($user['profile_picture']) ?>" alt="Profile" id="avatarPreview" class="profile-avatar-large">
                <div>
                    <label for="profile_picture" class="btn btn-outline btn-sm">
                        <i class="fas fa-camera"></i> Change Photo
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" hidden
                           onchange="previewAvatar(this)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required value="<?= sanitize($user['full_name']) ?>">
                </div>
                <div class="form-group">
                    <label>Email (cannot change)</label>
                    <input type="email" value="<?= sanitize($user['email']) ?>" disabled>
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required value="<?= sanitize($user['phone']) ?>">
            </div>

            <hr>
            <h3>Change Password (optional)</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" minlength="6" placeholder="Leave blank to keep current">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>

</div>
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

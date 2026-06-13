<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$chatUserId = (int) ($_GET['user'] ?? 0);
$productId = (int) ($_GET['product'] ?? 0);

$convStmt = $pdo->prepare(
    "SELECT u.id, u.full_name, u.profile_picture,
            (SELECT message FROM messages
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)
             ORDER BY created_at DESC LIMIT 1) AS last_message,
            (SELECT created_at FROM messages
             WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)
             ORDER BY created_at DESC LIMIT 1) AS last_time,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) AS unread
     FROM users u
     WHERE u.id IN (
         SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
         FROM messages WHERE sender_id = ? OR receiver_id = ?
     )
     ORDER BY last_time DESC"
);
$convStmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $convStmt->fetchAll();

$messages = [];
$chatUser = null;
$chatProduct = null;

if ($chatUserId) {
    $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $userStmt->execute([$chatUserId]);
    $chatUser = $userStmt->fetch();

    if ($chatUser) {
        $msgStmt = $pdo->prepare(
            'SELECT m.*, u.full_name AS sender_name
             FROM messages m JOIN users u ON m.sender_id = u.id
             WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC'
        );
        $msgStmt->execute([$userId, $chatUserId, $chatUserId, $userId]);
        $messages = $msgStmt->fetchAll();

        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0')
            ->execute([$chatUserId, $userId]);
    }

    if ($productId) {
        $prodStmt = $pdo->prepare('SELECT id, title FROM products WHERE id = ?');
        $prodStmt->execute([$productId]);
        $chatProduct = $prodStmt->fetch();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $chatUserId) {
    if (verifyCsrf($_POST['csrf_token'] ?? '')) {
        $message = trim($_POST['message'] ?? '');
        $refProduct = (int) ($_POST['product_id'] ?? 0) ?: null;

        if ($message !== '') {
            $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $chatUserId, $refProduct, sanitize($message)]);

            createNotification(
                $chatUserId, 'New Message',
                $_SESSION['user_name'] . ' sent you a message',
                'message',
                SITE_URL . '/pages/messages.php?user=' . $userId
            );

            redirect(SITE_URL . '/pages/messages.php?user=' . $chatUserId);
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="messages-page">
    <div class="conversations-panel">
        <h3><i class="fas fa-envelope"></i> Conversations</h3>
        <?php if (empty($conversations)): ?>
            <p class="no-conversations">No conversations yet. Contact a seller from a product page.</p>
        <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="<?= SITE_URL ?>/pages/messages.php?user=<?= $conv['id'] ?>"
                   class="conversation-item <?= $chatUserId == $conv['id'] ? 'active' : '' ?>">
                    <img src="<?= getProfileImage($conv['profile_picture']) ?>" alt="" class="conv-avatar">
                    <div class="conv-info">
                        <h4><?= sanitize($conv['full_name']) ?>
                            <?php if ($conv['unread'] > 0): ?>
                                <span class="badge"><?= $conv['unread'] ?></span>
                            <?php endif; ?>
                        </h4>
                        <p><?= sanitize(substr($conv['last_message'] ?? '', 0, 50)) ?></p>
                    </div>
                    <span class="conv-time"><?= $conv['last_time'] ? timeAgo($conv['last_time']) : '' ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-panel">
        <?php if ($chatUser): ?>
            <div class="chat-header">
                <img src="<?= getProfileImage($chatUser['profile_picture']) ?>" alt="" class="conv-avatar">
                <h3><?= sanitize($chatUser['full_name']) ?></h3>
            </div>

            <?php if ($chatProduct): ?>
                <div class="chat-product-ref">
                    <i class="fas fa-tag"></i> Regarding: <?= sanitize($chatProduct['title']) ?>
                </div>
            <?php endif; ?>

            <div class="chat-messages" id="chatMessages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-bubble <?= $msg['sender_id'] == $userId ? 'sent' : 'received' ?>">
                        <p><?= nl2br(sanitize($msg['message'])) ?></p>
                        <span class="msg-time"><?= timeAgo($msg['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" class="chat-input-form">
                <?= csrfField() ?>
                <?php if ($productId): ?>
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                <?php endif; ?>
                <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
            </form>
        <?php else: ?>
            <div class="chat-empty">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose a conversation from the left or contact a seller from a product page.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chat = document.getElementById('chatMessages');
    if (chat) chat.scrollTop = chat.scrollHeight;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

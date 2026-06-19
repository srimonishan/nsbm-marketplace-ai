<?php
/**
 * Admin Messages - NSBM Marketplace AI
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'messages';
$pageTitle = 'Messages';

// Get contact messages
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $messages = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted-custom mb-0"><?= count($messages) ?> messages</p>
</div>

<?php if (empty($messages)): ?>
    <div class="admin-card">
        <div class="admin-card-body text-center py-5">
            <i class="bi bi-envelope-open" style="font-size:3rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">No Messages</h5>
            <p class="text-muted-custom">Contact form submissions will appear here</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($messages as $msg): ?>
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><?= sanitize($msg['subject']) ?></h6>
                            <p class="text-muted-custom small mb-2">From: <strong><?= sanitize($msg['name']) ?></strong> (<?= sanitize($msg['email']) ?>)</p>
                            <p class="text-muted-custom mb-0"><?= sanitize($msg['message']) ?></p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted-custom"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></small>
                            <br>
                            <span class="status-badge status-<?= $msg['status'] ?? 'pending' ?>"><?= ucfirst($msg['status'] ?? 'new') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

session_start();

if (!isset($_SESSION['Team_id'])) {
    header('Location: ../teams.php');
    exit();
}

$teamId = (int) $_SESSION['Team_id'];
$notificationRepository = ServiceFactory::notificationRepository();

// Mark a single notification read when opened, or all as read.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read_id'])) {
        $notificationRepository->markRead((int) $_POST['mark_read_id'], 'team', $teamId);
    } elseif (isset($_POST['mark_all_read'])) {
        $notificationRepository->markAllRead('team', $teamId);
    }
    header('Location: notifications.php');
    exit();
}

$notifications = $notificationRepository->forRecipient('team', $teamId);

require 'header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Notifications</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <form method="post" class="d-inline">
                    <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline-secondary">
                        Mark all as read
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="alert alert-info">No notifications yet.</div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($notifications as $notification):
                                    $data = json_decode($notification['data'] ?? '[]', true) ?: [];
                                    $forRoles = $data['for_roles'] ?? [];
                                ?>
                                    <div class="list-group-item <?= $notification['is_read'] ? '' : 'list-group-item-warning' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?= htmlspecialchars($notification['title']) ?>
                                                    <?php if (!$notification['is_read']): ?>
                                                        <span class="badge bg-danger">Unread</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="mb-1" style="white-space: pre-line;"><?= htmlspecialchars($notification['body']) ?></p>
                                                <?php if (!empty($notification['ai_summary'])): ?>
                                                    <p class="mb-1"><strong>AI Summary:</strong> <em><?= htmlspecialchars($notification['ai_summary']) ?></em></p>
                                                <?php elseif (($notification['ai_summary_status'] ?? '') === 'pending'): ?>
                                                    <p class="mb-1"><small class="text-muted">AI summary generating...</small></p>
                                                <?php elseif (($notification['ai_summary_status'] ?? '') === 'failed'): ?>
                                                    <p class="mb-1"><small class="text-muted">AI summary unavailable</small></p>
                                                <?php endif; ?>
                                                <?php if (!empty($forRoles)): ?>
                                                    <small class="text-muted">For: <?= htmlspecialchars(implode(', ', $forRoles)) ?></small>
                                                <?php endif; ?>
                                                <br><small class="text-muted"><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></small>
                                            </div>
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="post">
                                                    <input type="hidden" name="mark_read_id" value="<?= (int) $notification['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Mark read</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>

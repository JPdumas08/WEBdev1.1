<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=notifications');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notification_id = (int) $_POST['notification_id'];
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = :nid AND user_id = :uid";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([':nid' => $notification_id, ':uid' => $user_id]);
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $update_all_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid";
    $stmt = $pdo->prepare($update_all_sql);
    $stmt->execute([':uid' => $user_id]);
}

// Handle delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notification_id = (int) $_POST['notification_id'];
    $delete_sql = "DELETE FROM notifications WHERE notification_id = :nid AND user_id = :uid";
    $stmt = $pdo->prepare($delete_sql);
    $stmt->execute([':nid' => $notification_id, ':uid' => $user_id]);
}

// Fetch notifications
$notifications_sql = "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC";
$stmt = $pdo->prepare($notifications_sql);
$stmt->execute([':uid' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$unread_count = count(array_filter($notifications, fn($n) => !$n['is_read']));

// Sample notifications if none exist (for demo)
if (empty($notifications)) {
    $sample_notifications = [
        ['title' => 'Welcome to Jeweluxe!', 'message' => 'Thank you for joining us. Start shopping for exquisite jewelry pieces.', 'type' => 'success'],
        ['title' => 'Order Updates', 'message' => 'We will notify you about your order status here.', 'type' => 'info'],
    ];
    
    foreach ($sample_notifications as $notif) {
        $insert_sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (:uid, :title, :message, :type)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([
            ':uid' => $user_id,
            ':title' => $notif['title'],
            ':message' => $notif['message'],
            ':type' => $notif['type']
        ]);
    }
    
    // Refresh notifications
    $stmt = $pdo->prepare($notifications_sql);
    $stmt->execute([':uid' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unread_count = count($notifications);
}
?>

<?php
$pageTitle = 'Notifications - Jeweluxe';
include 'includes/header.php';
?>
<link rel="stylesheet" href="styles.css">
<style>
    .notification-item {
            transition: background-color 0.2s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-unread {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
        }
        .notification-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
<body class="order-history-page">

    <section class="orders-hero">
        <div class="container-xl">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light btn-sm" onclick="window.history.back();" type="button" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1 class="mb-0 text-white">Notifications</h1>
            </div>
        </div>
    </section>

    <div class="orders-wrapper py-5">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h5 class="mb-0">All Notifications</h5>
                                <?php if ($unread_count > 0): ?>
                                    <small class="text-muted"><?php echo $unread_count; ?> unread</small>
                                <?php endif; ?>
                            </div>
                            <?php if ($unread_count > 0): ?>
                                <form method="POST" class="d-inline">
                                    <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline-primary">
                                        Mark All as Read
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($notifications)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3" style="font-size: 3rem;">🔔</div>
                                    <h5 class="mb-2">No notifications yet</h5>
                                    <p class="text-muted">We'll notify you when something arrives.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="list-group-item notification-item <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?> p-4">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                        <?php if (!$notification['is_read']): ?>
                                                            <span class="badge bg-primary notification-badge">New</span>
                                                        <?php endif; ?>
                                                        <?php
                                                        $badge_class = 'secondary';
                                                        switch ($notification['type']) {
                                                            case 'success': $badge_class = 'success'; break;
                                                            case 'warning': $badge_class = 'warning'; break;
                                                            case 'error': $badge_class = 'danger'; break;
                                                            case 'info': default: $badge_class = 'info'; break;
                                                        }
                                                        ?>
                                                        <span class="badge bg-<?php echo $badge_class; ?> notification-badge">
                                                            <?php echo ucfirst($notification['type']); ?>
                                                        </span>
                                                    </div>
                                                    <p class="mb-2 text-muted"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2 ms-3">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                            <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                                                ✓
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" class="d-inline delete-notification-form">
                                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-notification-btn" title="Delete">
                                                            ✕
                                                        </button>
                                                    </form>
                                                </div>
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

    <?php include 'includes/footer.php'; ?>
    <script>
        // Handle notification delete with custom confirmation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-notification-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('.delete-notification-form');
                    const notificationId = form.querySelector('input[name="notification_id"]').value;
                    
                    ConfirmModal.show(
                        '⚠️ Delete Notification',
                        'Are you sure you want to delete this notification?',
                        function() {
                            const formData = new FormData();
                            formData.append('notification_id', notificationId);
                            formData.append('delete_notification', '1');
                            
                            fetch('', {
                                method: 'POST',
                                body: formData
                            }).then(() => {
                                ToastNotification.success('Notification deleted successfully.');
                                setTimeout(() => location.reload(), 1500);
                            }).catch(error => {
                                ToastNotification.error('Error deleting notification.');
                                console.error('Error:', error);
                            });
                        }
                    );
                });
            });
        });
    </script>
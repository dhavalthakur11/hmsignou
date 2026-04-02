<?php /** Notification inbox */ ?>
<div class="card">
    <div class="card-header">
        <h3>Notifications</h3>
        <span class="badge badge-gray"><?= count($notifications) ?></span>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-icon">🔔</div>
                <h3>All caught up!</h3>
                <p>No notifications yet.</p>
            </div>
        <?php else: ?>
        <ul class="notif-list">
            <?php foreach ($notifications as $n): ?>
            <li class="notif-item">
                <div class="notif-icon">🔔</div>
                <div class="notif-body">
                    <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                    <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                    <div class="notif-time text-muted">
                        <?= date('d M Y, g:i a', strtotime($n['created_at'])) ?>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
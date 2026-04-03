<?php
/**
 * Customer Self-Service Dashboard
 */
?>

<!-- ── Welcome banner  -->
<div class="welcome-banner mb-4">
    <div class="welcome-text">
        <h2>Welcome back, <?= htmlspecialchars(explode(' ', user_name())[0]) ?>! 👋</h2>
        <p>Manage your bookings, explore rooms, and more.</p>
    </div>
    <a href="<?= base_url('booking/create') ?>" class="btn btn-primary">
        + Book a Room
    </a>
</div>

<!-- ── Summary cards  -->
<div class="stats-grid mb-4">
    <div class="stat-card stat-blue">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Bookings</span>
            <span class="stat-value"><?= (int)$total_bookings ?></span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Active</span>
            <span class="stat-value"><?= (int)$active_bookings ?></span>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Completed Stays</span>
            <span class="stat-value"><?= (int)$completed_bookings ?></span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Notifications</span>
            <span class="stat-value"><?= (int)$unread_notifs ?></span>
            <span class="stat-sub">unread</span>
        </div>
    </div>
</div>

<!-- ── Two columns: recent bookings + notifications  -->
<div class="two-col-grid mb-4">

    <!-- Recent bookings -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Bookings</h3>
            <a href="<?= base_url('booking/mybookings') ?>" class="link-sm">View all</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($my_bookings)): ?>
            <div class="empty-state" style="padding:28px">
                <div class="empty-icon">📅</div>
                <h3>No bookings yet</h3>
                <p>Ready for your first stay?</p>
                <a href="<?= base_url('booking/create') ?>" class="btn btn-primary btn-sm">
                    Book Now
                </a>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($my_bookings as $b):
                    $badge = match($b['status']) {
                        'confirmed'   => 'badge-blue',
                        'checked_in'  => 'badge-green',
                        'checked_out' => 'badge-gray',
                        'cancelled'   => 'badge-red',
                        default       => 'badge-gray',
                    };
                ?>
                <tr>
                    <td>#<?= (int)$b['booking_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($b['room_number']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($b['room_type']) ?></small>
                    </td>
                    <td><?= date('d M Y', strtotime($b['check_in'])) ?></td>
                    <td><span class="badge <?= $badge ?>">
                        <?= ucwords(str_replace('_', ' ', $b['status'])) ?>
                    </span></td>
                    <td>
                        <a href="<?= base_url('billing/invoice/' . $b['booking_id']) ?>"
                           class="btn btn-outline btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-header">
            <h3>Notifications</h3>
            <?php if ($unread_notifs > 0): ?>
            <span class="badge badge-blue"><?= $unread_notifs ?> new</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
            <div class="empty-state" style="padding:20px">
                <p>No notifications yet.</p>
            </div>
            <?php else: ?>
            <ul class="notif-list">
                <?php foreach (array_slice($notifications, 0, 8) as $n): ?>
                <li class="notif-item <?= !$n['is_read'] ? 'notif-unread' : '' ?>">
                    <div class="notif-icon">🔔</div>
                    <div class="notif-body">
                        <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                        <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif-time text-muted">
                            <?= date('d M, g:i a', strtotime($n['created_at'])) ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Available rooms to browse  -->
<?php if (!empty($featured_rooms)): ?>
<div class="card">
    <div class="card-header">
        <h3>Available Rooms</h3>
        <span class="badge badge-green"><?= count($featured_rooms) ?> available</span>
    </div>
    <div class="card-body">
        <div class="room-grid">
            <?php foreach (array_slice($featured_rooms, 0, 6) as $r): ?>
            <div class="room-card room-available">
                <div class="room-card-header">
                    <div class="room-number-badge">Room <?= htmlspecialchars($r['room_number']) ?></div>
                    <span class="badge badge-green">Available</span>
                </div>
                <div class="room-card-body">
                    <h3 class="room-type"><?= htmlspecialchars($r['room_type']) ?></h3>
                    <div class="room-meta">
                        <span>🏢 Floor <?= (int)$r['floor'] ?></span>
                        <span>👥 <?= (int)$r['capacity'] ?> guests</span>
                    </div>
                    <div class="room-price">
                        ₹<?= number_format((float)$r['price_per_night'], 0) ?>
                        <span class="price-unit">/ night</span>
                    </div>
                    <?php if (!empty($r['amenities'])): ?>
                    <div class="room-amenities">
                        <?php foreach (array_slice(explode(',', $r['amenities']), 0, 3) as $a): ?>
                        <span class="amenity-tag"><?= htmlspecialchars(trim($a)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="room-card-footer">
                    <a href="<?= base_url('booking/create?room_id=' . $r['room_id']) ?>"
                       class="btn btn-primary btn-sm" style="width:100%">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
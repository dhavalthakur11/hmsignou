<?php
/**
 * Admin Dashboard View
 * Stat cards + recent bookings table + activity log.
 */
?>

<!-- ── Stat Cards -->
<div class="stats-grid">

    <div class="stat-card stat-blue">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                <polyline points="9 21 9 12 15 12 15 21"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Rooms</span>
            <span class="stat-value"><?= (int)$total_rooms ?></span>
            <span class="stat-sub"><?= (int)$available_rooms ?> available</span>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Bookings</span>
            <span class="stat-value"><?= (int)$total_bookings ?></span>
            <span class="stat-sub"><?= (int)$active_bookings ?> active</span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Customers</span>
            <span class="stat-value"><?= (int)$total_customers ?></span>
            <span class="stat-sub"><?= (int)$total_employees ?> staff</span>
        </div>
    </div>

    <div class="stat-card stat-orange">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Revenue Today</span>
            <span class="stat-value">₹<?= number_format((float)$revenue_today, 0) ?></span>
            <span class="stat-sub">₹<?= number_format((float)$revenue_month, 0) ?> this month</span>
        </div>
    </div>

</div>

<!-- ── Room occupancy bar  -->
<?php
    $occupancyPct = $total_rooms > 0
        ? round(($booked_rooms / $total_rooms) * 100)
        : 0;
?>
<div class="card mt-4">
    <div class="card-header">
        <h3>Room Occupancy</h3>
        <span class="badge badge-blue"><?= $occupancyPct ?>% occupied</span>
    </div>
    <div class="card-body">
        <div class="occupancy-bar">
            <div class="occupancy-fill" style="width: <?= $occupancyPct ?>%"></div>
        </div>
        <div class="occupancy-legend">
            <span class="legend-dot dot-booked"></span> Booked (<?= (int)$booked_rooms ?>)
            &nbsp;&nbsp;
            <span class="legend-dot dot-available"></span> Available (<?= (int)$available_rooms ?>)
            &nbsp;&nbsp;
            <span class="legend-dot dot-maintenance"></span>
            Maintenance (<?= max(0, (int)$total_rooms - (int)$booked_rooms - (int)$available_rooms) ?>)
        </div>
    </div>
</div>

<!-- ── Two-column: recent bookings + recent activity  -->
<div class="two-col-grid mt-4">

    <!-- Recent bookings -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Bookings</h3>
            <a href="<?= base_url('booking/index') ?>" class="link-sm">View all</a>
        </div>
        <div class="card-body p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_bookings)): ?>
                        <tr><td colspan="5" class="empty-row">No bookings yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $b): ?>
                        <tr>
                            <td>#<?= (int)$b['booking_id'] ?></td>
                            <td><?= htmlspecialchars($b['guest_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($b['room_number'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($b['check_in'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= match($b['status'] ?? '') {
                                    'confirmed'  => 'green',
                                    'checked_in' => 'blue',
                                    'checked_out'=> 'gray',
                                    'cancelled'  => 'red',
                                    default      => 'gray'
                                } ?>">
                                    <?= ucfirst(str_replace('_', ' ', $b['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent audit log -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Activity</h3>
            <a href="<?= base_url('logs/index') ?>" class="link-sm">View all</a>
        </div>
        <div class="card-body">
            <ul class="activity-list">
                <?php if (empty($recent_logs)): ?>
                    <li class="empty-row">No activity yet.</li>
                <?php else: ?>
                    <?php foreach ($recent_logs as $log): ?>
                    <li class="activity-item">
                        <span class="activity-dot dot-<?= str_contains($log['action'], 'FAIL') ? 'red' : 'green' ?>"></span>
                        <div class="activity-body">
                            <span class="activity-action"><?= htmlspecialchars($log['action']) ?></span>
                            <span class="activity-meta">
                                <?= htmlspecialchars($log['user_name'] ?? 'System') ?>
                                &mdash; <?= htmlspecialchars($log['created_at'] ?? '') ?>
                            </span>
                            <?php if ($log['detail']): ?>
                                <span class="activity-detail"><?= htmlspecialchars($log['detail']) ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</div>

<!-- ── Quick actions  -->
<div class="card mt-4">
    <div class="card-header"><h3>Quick Actions</h3></div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="<?= base_url('room/create') ?>"    class="quick-btn">+ Add Room</a>
            <a href="<?= base_url('booking/create') ?>" class="quick-btn">+ New Booking</a>
            <a href="<?= base_url('employee/create') ?>" class="quick-btn">+ Add Employee</a>
            <a href="<?= base_url('report/index') ?>"   class="quick-btn quick-btn-outline">View Reports</a>
        </div>
    </div>
</div>
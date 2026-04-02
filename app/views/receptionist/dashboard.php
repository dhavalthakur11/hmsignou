<?php
/**
 * Receptionist / Front-Desk Dashboard
 * Today's arrivals & departures + live room grid.
 */
?>

<!-- ── Stat row ──────────────────────────────────────────────── -->
<div class="stats-grid mb-4">
    <div class="stat-card stat-green">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M15 3h6v6M14 10l7-7M9 21H3v-6M10 14l-7 7"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Arrivals Today</span>
            <span class="stat-value"><?= (int)$stats['arrivals_today'] ?></span>
        </div>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M9 21H3v-6M10 14l-7 7M15 3h6v6M14 10l7-7"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Departures Today</span>
            <span class="stat-value"><?= (int)$stats['departures_today'] ?></span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                <polyline points="9 21 9 12 15 12 15 21"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Available Rooms</span>
            <span class="stat-value"><?= (int)$stats['available'] ?></span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Occupied Rooms</span>
            <span class="stat-value"><?= (int)$stats['booked'] ?></span>
        </div>
    </div>
</div>

<!-- ── Two-column: arrivals + departures ────────────────────── -->
<div class="two-col-grid mb-4">

    <!-- Today's arrivals -->
    <div class="card">
        <div class="card-header">
            <h3>
                <span class="dot-indicator dot-green"></span>
                Today's Arrivals
            </h3>
            <span class="badge badge-green"><?= count($arrivals) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($arrivals)): ?>
                <div class="empty-state" style="padding:28px">
                    <p>No arrivals scheduled for today.</p>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Guests</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($arrivals as $a): ?>
                <tr>
                    <td>#<?= (int)$a['booking_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($a['guest_name']) ?></strong>
                        <?php if ($a['guest_phone']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($a['guest_phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($a['room_number']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($a['room_type']) ?></small>
                    </td>
                    <td><?= (int)$a['guests'] ?></td>
                    <td>
                        <?php if ($a['status'] === 'confirmed'): ?>
                        <form method="POST"
                              action="<?= base_url('booking/checkin/' . $a['booking_id']) ?>">
                            <button class="btn btn-success btn-sm">Check In</button>
                        </form>
                        <?php else: ?>
                            <span class="badge badge-blue">Checked In</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Today's departures -->
    <div class="card">
        <div class="card-header">
            <h3>
                <span class="dot-indicator dot-blue"></span>
                Today's Departures
            </h3>
            <span class="badge badge-blue"><?= count($departures) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($departures)): ?>
                <div class="empty-state" style="padding:28px">
                    <p>No departures scheduled for today.</p>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Guests</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($departures as $d): ?>
                <tr>
                    <td>#<?= (int)$d['booking_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($d['guest_name']) ?></strong>
                        <?php if ($d['guest_phone']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($d['guest_phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($d['room_number']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($d['room_type']) ?></small>
                    </td>
                    <td><?= (int)$d['guests'] ?></td>
                    <td>
                        <form method="POST"
                              action="<?= base_url('booking/checkout/' . $d['booking_id']) ?>">
                            <button class="btn btn-primary btn-sm">Check Out</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Live Room Grid ─────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h3>Live Room Status</h3>
        <div style="display:flex;gap:12px;font-size:.8rem;align-items:center">
            <span><span class="legend-dot dot-available"></span> Available</span>
            <span><span class="legend-dot dot-booked"></span> Occupied</span>
            <span><span class="legend-dot dot-maintenance"></span> Maintenance</span>
        </div>
    </div>
    <div class="card-body">
        <div class="room-status-grid">

            <?php foreach ($available_rooms as $r): ?>
            <a href="<?= base_url('booking/create?room_id=' . $r['room_id']) ?>"
               class="room-status-cell room-cell-available"
               title="Room <?= htmlspecialchars($r['room_number']) ?> — Available">
                <span class="room-cell-number"><?= htmlspecialchars($r['room_number']) ?></span>
                <span class="room-cell-type"><?= htmlspecialchars($r['room_type']) ?></span>
                <span class="room-cell-price">₹<?= number_format((float)$r['price_per_night'], 0) ?></span>
            </a>
            <?php endforeach; ?>

            <?php foreach ($booked_rooms as $r): ?>
            <div class="room-status-cell room-cell-booked"
                 title="Room <?= htmlspecialchars($r['room_number']) ?> — Occupied">
                <span class="room-cell-number"><?= htmlspecialchars($r['room_number']) ?></span>
                <span class="room-cell-type"><?= htmlspecialchars($r['room_type']) ?></span>
                <span class="room-cell-status">Occupied</span>
            </div>
            <?php endforeach; ?>

            <?php foreach ($maintenance_rooms as $r): ?>
            <div class="room-status-cell room-cell-maintenance"
                 title="Room <?= htmlspecialchars($r['room_number']) ?> — Maintenance">
                <span class="room-cell-number"><?= htmlspecialchars($r['room_number']) ?></span>
                <span class="room-cell-type"><?= htmlspecialchars($r['room_type']) ?></span>
                <span class="room-cell-status">Maint.</span>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>